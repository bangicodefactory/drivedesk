# Deploy `drivedesk.ma` alongside `directonderweg` on the same cPanel host

DriveDesk is the product's **demo/showcase client** (`demo_gateway` on). This
guide adds it as a **second, fully isolated app on the same Namecheap cPanel
account** that already runs `directonderweg.com` — without touching the
directonderweg production app.

It assumes the directonderweg deploy is already working (see
`docs/deploy-namecheap-cpanel.md` and `docs/deploy-directonderweg-com.md`). Only
the **per-client** pieces are new; the account-level setup (SSH key, PHP 8.4,
composer) is shared and already done.

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

> If LiteSpeed serves a blank/static page from that docroot (it mis-handled a
> *symlinked* docroot for directonderweg), use the same **bridge docroot**
> workaround documented in `docs/deploy-directonderweg-com.md` (a real docroot
> dir with a small `index.php` that requires the app's bootstrap, per-asset
> symlinks to `public/build`, and a hand-written `.htaccess`). A docroot set
> directly to the real `…/drivedesk/public` path usually avoids it.

## 3. Put the app on the host

```bash
cd ~
git clone https://github.com/bangicodefactory/rentcar.git drivedesk
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
(`deploy.yml` already routes a `drivedesk/vX.Y.Z` tag here — no workflow change
needed.) Add required reviewers if you want a manual gate.

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
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` | **real** SMTP for `drivedesk.ma` (Namecheap Private Email, e.g. `no-reply@drivedesk.ma`) — **mandatory**, see §2.2 of the main runbook: the demo "Book a demo" form and the approval credentials email send inline, so bad SMTP is a user-facing 500 |
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
git tag drivedesk/v1.0.0
git push origin drivedesk/v1.0.0
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
