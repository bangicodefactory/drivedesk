# Deploy `drivedesk.ma` (Namecheap cPanel)

DriveDesk is the product's **demo/showcase client** (`demo_gateway` on). It runs
as a **fully isolated app on a shared Namecheap cPanel account** that also hosts
another Laravel app (`directonderweg.com`, deployed from its own repo,
`bangicodefactory/rentcar`). Nothing in this guide touches that app.

The account-level setup (SSH key, PHP 8.4, composer) is shared and already
done; only the **per-app** pieces below are DriveDesk's. Appendices A–C cover
the host quirks (no Redis, SMTP for the demo form, LiteSpeed bridge docroot).

Replace `CPUSER` with the cPanel username (`direxjym`). The host's public IP is
the same one directonderweg resolves to (today `162.0.217.220`).

---

## 0. The isolation contract (read this first)

Two apps share **one box, one MySQL server, one SSH user, one PHP, one web
server**. Everything else is **separate** — that is what keeps directonderweg
safe:

| Per-client resource | directonderweg | drivedesk |
| --- | --- | --- |
| Domain | `directonderweg.com` | **`drivedesk.ma`** |
| App dir (`DEPLOY_PATH`) | `~/directonderweg` | **`~/drivedesk`** |
| Document root | its docroot | **its own** (the `drivedesk.ma` addon-domain root → app `public/`) |
| Database | its DB | **new** `CPUSER_drivedesk` + its **own** DB user |
| `.env` `APP_KEY` | reuses the old host's key | **brand-new key** (fresh app, no shared encrypted data — never reuse directonderweg's) |
| `.env` `APP_CLIENT` | `directonderweg` | **`drivedesk`** |
| Scheduler cron | one `schedule:run` for its dir | **a second** `schedule:run` for `~/drivedesk` |
| GitHub Environment | `production-directonderweg` | **`production-drivedesk`** |
| SSL cert | its AutoSSL | **its own** AutoSSL for `drivedesk.ma` |

> ⚠️ **The two values that must differ** between the GitHub Environments are
> `DEPLOY_PATH` and `DB_DATABASE`. Get those right and isolation is total: the
> deploy `cd`s into `${DEPLOY_PATH}` and only ever backs up / migrates the DB
> named in **that app's** `.env`. Get them wrong and you'd overwrite prod —
> double-check before the first deploy.

DriveDesk is a **fresh** app: its data is **not** copied from anywhere. The
pipeline runs migrations on an empty DB, seeds branding (`client:install`), and
seeds the showcase data (`demo:seed`). So there is **no DB/storage copy step**
(unlike the directonderweg migration).

---

## 1. DNS — point `drivedesk.ma` at the host

At your domain registrar for `drivedesk.ma`, add A records to the host IP:

```
@      A   162.0.217.220
www    A   162.0.217.220
```

(Use the same IP `directonderweg.com` resolves to. Allow time for propagation.)

## 2. Add `drivedesk.ma` as an addon domain (cPanel)

cPanel → **Domains → Create a New Domain**:

- Domain: `drivedesk.ma`
- **Uncheck** "share document root"; set the **Document Root** to
  **`/home/CPUSER/drivedesk/public`** (we create that app dir next).

> If LiteSpeed serves a blank/static page from that docroot (it mis-handles a
> *symlinked* docroot), use the **bridge docroot** workaround in Appendix C.
> That is what `drivedesk.ma` runs today.

## 3. Put the app on the host

```bash
cd ~
git clone git@github.com:bangicodefactory/drivedesk.git drivedesk
# private repo: clone over SSH using the existing deploy key (already authorized
# for directonderweg); the per-deploy `git fetch` reuses it.
```

App path is `/home/CPUSER/drivedesk` → this is `DEPLOY_PATH` in step 5.

`public/.htaccess` is **not** in the repo (same as directonderweg). Create it:

```bash
cat > ~/drivedesk/public/.htaccess <<'HTACCESS'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTACCESS
```

## 4. Create the database (empty — fresh app)

cPanel → **MySQL Databases**:

1. Create database → it becomes **`CPUSER_drivedesk`**.
2. Create a **new** DB user (e.g. `CPUSER_dduser`) with a strong password —
   **its own user**, not directonderweg's, scoped to this DB only.
3. **Add User To Database** → All Privileges.

These three values are the `DB_*` secrets in step 5. Do **not** import any dump.

## 5. Create the `production-drivedesk` GitHub Environment

Repo → **Settings → Environments → New environment → `production-drivedesk`**.
(`deploy.yml` routes a bare `vX.Y.Z` tag — drivedesk is the default client —
and the explicit `drivedesk/vX.Y.Z` form here; no workflow change needed.) Add required reviewers if you want a manual gate.

**Secrets** (encrypted):

| Secret | Value |
| --- | --- |
| `SSH_HOST` | same host as directonderweg (server hostname or `drivedesk.ma`) |
| `SSH_USERNAME` | `CPUSER` (same account) |
| `SSH_PRIVATE_KEY` | the **same** deploy private key already authorized on the account |
| `APP_KEY` | **generate a NEW key** — `php artisan key:generate --show` — do **not** reuse directonderweg's |
| `DB_HOST` | `localhost` |
| `DB_DATABASE` | `CPUSER_drivedesk` ← **must differ from directonderweg** |
| `DB_USERNAME` | `CPUSER_dduser` |
| `DB_PASSWORD` | the step-4 password |
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` | **real** SMTP for `drivedesk.ma` (Namecheap Private Email, e.g. `no-reply@drivedesk.ma`) — **mandatory**, see Appendix B: the demo "Book a demo" form and the approval credentials email send inline, so bad SMTP is a user-facing 500 |
| `NOCAPTCHA_SITEKEY` / `NOCAPTCHA_SECRET` | reCAPTCHA keys whose allowed-domains include **`drivedesk.ma`** (login uses reCAPTCHA) |
| `SENTRY_LARAVEL_DSN` | (optional) |

**Variables** (plain):

| Var | Value |
| --- | --- |
| `DEPLOY_PATH` | `/home/CPUSER/drivedesk` ← **must differ from directonderweg** |
| `APP_URL` | `https://drivedesk.ma` |
| `APP_NAME` | `DriveDesk` |
| `APP_CLIENT` | **`drivedesk`** |
| `APP_ENV` | `production` |
| `CACHE_STORE` | `file` (no Redis on shared hosting) |
| `SESSION_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `MAIL_PORT` / `MAIL_FROM_ADDRESS` | `587` / `no-reply@drivedesk.ma` |
| `SENTRY_ENVIRONMENT` | `production` |
| `SSH_PORT` | **`21098`** (Namecheap) |

Notes:
- `deploy.yml` writes `MAIL_MAILER=smtp` and `INERTIA_SSR_ENABLED=false` into the
  generated `.env` automatically — no var needed.
- `demo_gateway` is already `true` in `config/clients/drivedesk.php`; no
  `FEATURE_*` override needed.

## 6. First deploy

```bash
git tag v1.0.38
git push origin v1.0.38
```

The pipeline: resolves `production-drivedesk` → runs the full per-client CI
matrix as the release gate → SSHes in, `cd ~/drivedesk`, pulls the tagged SHA,
`composer install --no-dev`, **backs up `CPUSER_drivedesk` only**, `migrate
--force`, `client:install`, **`demo:seed --if-demo`** (seeds the showcase data —
this is a demo client), caches config/routes/views, `queue:restart`, `up`, then
hits `https://drivedesk.ma/login` as a health check.

> The pre-deploy `mysqldump` and all artisan commands read **drivedesk's**
> `.env`, so this run cannot read or write directonderweg's DB or files. Its
> `php artisan down/up` marker lives in `~/drivedesk` only — directonderweg
> stays up throughout.

## 7. Scheduler cron (nightly demo refresh + reminders)

Add a **second** cron (cPanel → **Cron Jobs**, every minute) for the drivedesk
app — independent of directonderweg's:

```
* * * * * cd /home/CPUSER/drivedesk && /home/CPUSER/bin/php artisan schedule:run >> /dev/null 2>&1
```

This runs DriveDesk's reminder jobs **and** the nightly `demo:seed --if-demo`
(03:30) that re-anchors the demo data to "today" and resets the sandbox.

## 8. HTTPS for `drivedesk.ma`

cPanel → **SSL/TLS Status** → select `drivedesk.ma` (and `www`) → **Run
AutoSSL**. Once issued, confirm `https://drivedesk.ma` loads. `APP_URL` is
already `https://…`, and Sanctum derives its stateful domain from `APP_URL`, so
login works on the new domain with no extra config.

## 9. Verify (and prove isolation)

- `https://drivedesk.ma/` → the DriveDesk marketing landing (not a login
  redirect — `demo_gateway` is on for this client).
- Submit **Book a demo** → success state; the request emails `admin@bangicode.ma`
  and creates a pending login.
- Log in as the super-admin → **Demo requests** → Approve one → the prospect
  gets a real set-password email (this is why SMTP must be live).
- `https://directonderweg.com/` → **unchanged**, still up. (It was never touched:
  different dir, different DB, its own maintenance-mode marker.)

---

## Caveats on a shared box

- **Shared resources** (CPU/RAM/disk I/O, the MySQL server). A public demo
  gateway attracts bots; a spike could marginally affect directonderweg's
  responsiveness. For a low-traffic demo it's negligible — but if directonderweg
  is performance-sensitive, consider a separate small host or rate-limiting
  `/demo-request`.
- **Disk quota** — the nightly `demo:seed` + per-deploy DB backups add some
  usage; glance at the account quota.
- **Separate DB user** — drivedesk's DB user is scoped to `CPUSER_drivedesk`
  only, so a credential leak can't reach directonderweg's data.
- **Fresh `APP_KEY`** — drivedesk encrypts its own (new) data; reusing
  directonderweg's key would be both wrong and a cross-app secret leak.

## Adding more demo/real clients later

Same pattern, no workflow change: commit `config/clients/<client>.php` (+
`app/Clients/<Client>/` + CI matrix entry), add the addon domain + DB + dir +
cron, create `production-<client>`, and tag `<client>/vX.Y.Z`.


---

## Appendix A — No-Redis option (host without Redis)

`deploy.yml` **defaults all three drivers to `redis`**. On a host without
Redis you must override them in the Environment **vars**, or the app throws
connection-refused on cache/session/queue (a hard break, not just slowness):

| Var | No-Redis value |
| --- | --- |
| `CACHE_STORE` | `file` |
| `SESSION_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `sync` (simplest — see below) |

Leave `REDIS_HOST`/`REDIS_PORT` unset. At this app's scale (single host) the
impact is negligible: the only hot cache use is the `settings()` helper, and
`file` sessions are fine on one host. With `sync`, the `ShouldQueue` mailables
send **inline in the request** (~0.5–2s SMTP round-trip on send-agreement /
verification emails). If that becomes a problem, switch
`QUEUE_CONNECTION=database` — but that needs a `jobs` table
(`php artisan queue:table` + `migrate`) **and** a running worker.

> ⚠️ With `sync`, if SMTP is down/slow the failure surfaces **on the user's
> request**. Make sure `MAIL_*` is solid before choosing `sync`.

## Appendix B — SMTP is mandatory for the demo gateway

The public "Book a demo" form on `/` emails the product inbox configured as
`demo_request_to` in `config/clients/drivedesk.php` (today
`admin@bangicode.ma`). Unlike the other mailables, `App\Mail\DemoRequest` and
`App\Mail\DemoCredentials` are **not** `ShouldQueue` — they send **inline
regardless of `QUEUE_CONNECTION`**, so a missing or wrong `MAIL_*` config is
**always a user-facing 500 on the demo form**, not a silent background failure.
(Seen locally: `MAIL_USERNAME`/`MAIL_PASSWORD` `null` → SMTP `530 Authentication
required` → 500.)

All of these must be set and verified **before** go-live:

| Var / Secret | Notes |
| --- | --- |
| `MAIL_MAILER` | `smtp` (must **not** be `log` in production — `send()` then "succeeds" and nothing is delivered) |
| `MAIL_HOST` | SMTP host that can send as the `MAIL_FROM_ADDRESS` domain |
| `MAIL_PORT` | `587` (STARTTLS) or `465` (TLS) |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | **Real** SMTP credentials — never `null` |
| `MAIL_ENCRYPTION` | `tls` (or `ssl` for port 465) |
| `MAIL_FROM_ADDRESS` | A mailbox the SMTP account is allowed to send from |

Smoke-test after deploy: open `/`, submit the demo form, confirm the success
state **and that the email actually arrives** in the inbox.

### Demo showcase data seeds and refreshes itself

- **On every deploy**, `deploy.yml` runs `php artisan demo:seed --if-demo`
  right after `client:install`. It's best-effort: a failure logs a warning but
  never aborts the deploy.
- **Nightly** (`03:30`, Laravel scheduler — needs the host cron running
  `schedule:run`), `demo:seed --if-demo` re-anchors the time-relative data to
  "today" and resets the sandbox.

`demo:seed` is idempotent and **gated to `feature('demo_gateway')`**; it refuses
to run on a non-demo client unless `--force` (and never in production).

> ⚠️ The nightly run **wipes the demo tenant's transactional data** (bookings,
> payments, expenses, reminders, agreements, credits, TVA — scoped to the demo
> owner) and reseeds it. Catalog data (vehicles, places, addons) is preserved.
> Change the cadence in `app/Console/Kernel.php`.

## Appendix C — LiteSpeed "bridge docroot" workaround

On this host LiteSpeed serves a blank page when an addon domain's document root
is a **symlink** to `~/drivedesk/public`. The workaround is a real directory
(`~/drivedesk.ma`) set as the docroot, containing:

1. **`index.php`** that requires the app's bootstrap by absolute path — the
   stock `public/index.php` with its paths rewritten:

   ```php
   <?php
   use Illuminate\Http\Request;
   define('LARAVEL_START', microtime(true));
   $app = '/home/CPUSER/drivedesk';
   if (file_exists($app.'/storage/framework/maintenance.php')) {
       require $app.'/storage/framework/maintenance.php';
   }
   require $app.'/vendor/autoload.php';
   (require_once $app.'/bootstrap/app.php')
       ->handleRequest(Request::capture());
   ```

2. **Per-asset symlinks** into the real `public/` (`build`, `images`,
   `favicon.ico`, `robots.txt`, `storage` → `../drivedesk/public/…`). Symlinking
   *files and subdirectories* works; symlinking the docroot itself does not.
3. The standard Laravel **`.htaccess`** (step 3 above), hand-copied.

Things that bit on first deploy — check them on a fresh clone:

- `storage/framework/{cache/data,sessions,views}` and `bootstrap/cache` are
  **not in git**; without them `artisan down` 500s.
- `storage/installed` marker — `rachidlaasri/laravel-installer` redirects `/` to
  `/install` without it.
- `APP_KEY` must be `base64:` + 32 bytes; a malformed key is a 500
  "Unsupported cipher".
- A foreign `Host` header gets a **404 from LiteSpeed** (unknown vhost) before
  Laravel's `TrustHosts` 400 ever fires — the app-level guard is a second layer
  you cannot observe from outside.
