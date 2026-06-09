# Runbook — point `directonderweg.com` at the existing app + database

**Goal:** bring the `directonderweg` client live on the new domain
**`https://directonderweg.com`**, reusing the **same database** that the old
`https://directonderweg.ma` site already uses. `.com` **replaces** `.ma`
(`.ma` is retired / 301-redirects to `.com`).

**Strategy (per `CLAUDE.md` §10.3):** trunk-based, **tag-driven** deploys to a
per-client **GitHub Environment**. There is **no per-client branch** — the
"dedicated unit" for this client is the `production-directonderweg`
Environment. A production deploy = push a `vX.Y.Z` tag, which runs
`.github/workflows/deploy.yml` against `production-directonderweg`.

Because `.com` replaces `.ma` on the **same server + same DB**, this is a
**configuration change only** — no application code changes are required.

---

## 0. Prerequisites / decisions (already settled)

| Decision | Value |
| --- | --- |
| Domain plan | `.com` **replaces** `.ma` (same server, same DB; repoint DNS) |
| Deploy trigger | **Tag-based** (`v*`) → `production-directonderweg` Environment |
| New branch? | **No** (§10.3 — do not create a `client/...` long-lived branch) |
| CI | Already runs for `directonderweg` (`ci.yml`) — nothing to add |

---

## 1. GitHub Environment — `production-directonderweg`

`deploy.yml` writes the server `.env` entirely from this Environment's
**secrets** and **vars**. Set them under
**Repo → Settings → Environments → `production-directonderweg`**.

> ⚠️ **Reuse the OLD `.ma` values** for `APP_KEY` and all `DB_*`. See §2.

### Secrets (sensitive — encrypted)

| Secret | Value for `.com` |
| --- | --- |
| `APP_KEY` | **Exact same key as the old `.ma` app** (see §2) |
| `DB_HOST` | The existing DB host (same DB as `.ma`) |
| `DB_DATABASE` | The existing schema name |
| `DB_USERNAME` | The existing DB user |
| `DB_PASSWORD` | The existing DB password |
| `MAIL_HOST` | SMTP host (e.g. `smtp.gmail.com`) |
| `MAIL_USERNAME` | SMTP user |
| `MAIL_PASSWORD` | SMTP password / app password |
| `NOCAPTCHA_SITEKEY` | reCAPTCHA site key (see §4 — must allow `directonderweg.com`) |
| `NOCAPTCHA_SECRET` | reCAPTCHA secret |
| `SENTRY_LARAVEL_DSN` | (optional) Sentry DSN |
| `SSH_HOST` | Production server host/IP |
| `SSH_USERNAME` | Deploy SSH user |
| `SSH_PRIVATE_KEY` | Deploy SSH private key |

### Vars (non-sensitive)

| Var | Value for `.com` |
| --- | --- |
| `APP_URL` | **`https://directonderweg.com`**  ← the key change |
| `APP_NAME` | `Direct Onderweg` |
| `APP_CLIENT` | `directonderweg` |
| `APP_ENV` | `production` |
| `CACHE_STORE` | `redis` (or `file` if no Redis) |
| `SESSION_DRIVER` | `redis` (or `file`) |
| `QUEUE_CONNECTION` | `redis` (or `database`) |
| `REDIS_HOST` / `REDIS_PORT` | `127.0.0.1` / `6379` (if used) |
| `MAIL_PORT` | `587` |
| `MAIL_FROM_ADDRESS` | e.g. `no-reply@directonderweg.com` |
| `SENTRY_ENVIRONMENT` | `production` |
| `SSH_PORT` | `22` (or custom) |
| `DEPLOY_PATH` | Absolute path of the app on the server (e.g. `/var/www/directonderweg`) |

> **Auth on the new domain works automatically.** This is a same-origin
> Inertia + Sanctum app, and `config/sanctum.php` derives its stateful
> domains from `APP_URL` while `SESSION_DOMAIN` defaults to the current host.
> So setting `APP_URL=https://directonderweg.com` is sufficient — no
> `SANCTUM_STATEFUL_DOMAINS` / `SESSION_DOMAIN` overrides needed.

---

## 2. ⚠️ Reuse the old `APP_KEY` (do not generate a new one)

The new deploy points at the **old database**. If the old app encrypted
anything at rest (encrypted casts, signed/“remember me” cookies, signed
URLs), a **different `APP_KEY` would fail to decrypt it**. Copy the
**exact** `APP_KEY` from the old `.ma` server's `.env` into the
`production-directonderweg` Environment. Generating a fresh key (`php artisan
key:generate`) here would be a mistake.

To read it from the old server: `grep ^APP_KEY= /path/to/old/.env`.

---

## 3. DNS + web server (on the host)

1. **DNS:** point `directonderweg.com` (A/AAAA, and `www` if used) at the
   production server IP.
2. **Web server vhost:** add `directonderweg.com` (and `www.`) as the
   `server_name` for the existing app's docroot (`.../public`).
3. **TLS:** issue a certificate for `directonderweg.com`
   (e.g. `certbot --nginx -d directonderweg.com -d www.directonderweg.com`).
4. **Retire `.ma`:** add a 301 redirect from `directonderweg.ma` →
   `https://directonderweg.com` (keep `.ma`'s cert until DNS/redirect settle).
5. **Scheduler cron (required):** the server must run Laravel's scheduler, or
   none of the scheduled commands fire — reminder status/recurring jobs **and**
   the nightly `logged_histories` prune (F-19). Add one cron entry:
   `* * * * * cd <DEPLOY_PATH> && php artisan schedule:run >> /dev/null 2>&1`.
   (A queue worker for `redis` is also expected — `deploy.yml` runs
   `php artisan queue:restart` on each deploy.)

---

## 4. reCAPTCHA (login page)

reCAPTCHA keys are domain-scoped. In the Google reCAPTCHA admin console, add
`directonderweg.com` to the key's allowed domains (or mint a new key pair and
update `NOCAPTCHA_SITEKEY` / `NOCAPTCHA_SECRET`). Otherwise the login captcha
fails on the new domain.

---

## 5. First deploy

Since `.com` reuses the already-migrated DB on the same server, the deploy is
effectively a config refresh. From `dev`/`main`:

```bash
git tag v1.0.0          # use the real next version
git push origin v1.0.0  # → triggers deploy.yml → production-directonderweg
```

`deploy.yml` will: build assets, SCP them + the generated `.env`, then on the
server `composer install`, `php artisan migrate --force` (a no-op if already
migrated), `php artisan client:install` (idempotent branding seed),
`config/route/view:cache`, and `queue:restart`.

> You can dry-run against staging first via **Actions → Deploy → Run workflow
> → `staging-directonderweg`** (needs a `staging-directonderweg` Environment).

---

## 6. Smoke test (after deploy)

- `https://directonderweg.com` loads over HTTPS (valid cert, no mixed content).
- Log in (captcha works); the dashboard shows the **existing** data (confirms
  the same DB).
- One write (create/edit a booking) persists.
- A test email sends (Settings → SMTP → Send Test).
- `https://directonderweg.ma` 301-redirects to `.com`.

---

## 7. Rollback

Re-deploy the previous good tag:

```bash
# Actions → Deploy → Run workflow, or:
git push origin <previous-good-tag> --force-with-lease   # if re-pointing
```

The SSH script checks out a specific `github.sha`, so deploying an earlier tag
rolls the code back. The shared DB is **not** rolled back automatically — avoid
destructive migrations, and back up the DB before any release that alters
schema.

---

## What needs whom

| Task | Owner |
| --- | --- |
| Set `production-directonderweg` Environment secrets/vars (§1–2) | Repo admin |
| DNS + vhost + TLS + `.ma` redirect (§3) | Host / devops |
| reCAPTCHA domain (§4) | Whoever owns the Google account |
| Push the release tag (§5) | Maintainer |
| This runbook | In repo (`docs/deploy-directonderweg-com.md`) |
