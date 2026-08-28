# DriveDesk

Car rental management software for rental agencies (Morocco-first), sold as
**DriveDesk** with a public demo at [drivedesk.ma](https://drivedesk.ma).
Multi-client Laravel platform: one codebase, one isolated deployment per
client.
Features include vehicle and driver management, bookings, rental agreements
with digital signatures, inspections, expenses, credits, TVA (VAT) handling,
multi-locale support, reCAPTCHA, and role/permission management.

The repo is `bangicodefactory/drivedesk`. The active client is selected at
deploy time via `APP_CLIENT=drivedesk` (the default). See
`docs/client-configurability.md` for the multi-client architecture.

---

## Tech stack

| Layer | Technology |
| --- | --- |
| PHP | **8.3+** (CI runs 8.4) |
| Laravel | **12** |
| Database | MySQL **8.0+** (MariaDB 10.6+ also works) |
| Node | **20+ LTS** |
| Frontend | Inertia.js + **React 19** (JSX, no TypeScript) |
| Build | **Vite** |
| CSS | **Tailwind 4** |
| UI components | **shadcn/ui** (Radix UI primitives — live in `resources/js/components/ui/`) |
| Forms | react-hook-form + zod |
| Auth | Laravel Sanctum + Breeze (Inertia/React stack) |

Key packages: `spatie/laravel-permission`, `laravel/sanctum`,
`barryvdh/laravel-dompdf`, `creagia/laravel-sign-pad`,
`anhskohbo/no-captcha` (reCAPTCHA v2),
`kkomelin/laravel-translatable-string-exporter`,
`rachidlaasri/laravel-installer`, `phpoffice/phpspreadsheet`.

---

## Local development setup

### 1. Prerequisites

- PHP 8.3+ (8.4 recommended)
- Composer 2.x
- Node.js 20+ and npm 9+
- MySQL 8 or MariaDB 10.6+
- (optional) Redis — for cache/queue/session
- (optional) [Mailpit](https://github.com/axllent/mailpit) — catches dev email

On macOS (Homebrew):

```bash
brew install php@8.3 composer node@20 mysql mailpit
```

On Windows: [Laragon](https://laragon.org/) or WSL2 + Ubuntu.

### 2. Clone and install

```bash
git clone git@github.com:bangicodefactory/rentcar.git
cd rentcar

composer install
npm ci
cp .env.example .env
php artisan key:generate
```

### 3. Configure `.env`

Minimum required:

```dotenv
APP_NAME="DriveDesk"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rentcar
DB_USERNAME=root
DB_PASSWORD=

# Active client for this deployment
APP_CLIENT=drivedesk

# Mail (Mailpit defaults)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@drivedesk.local"
MAIL_FROM_NAME="${APP_NAME}"

# Queue — 'sync' is fine locally
QUEUE_CONNECTION=sync

# Cache / Session — 'file' is fine locally
CACHE_STORE=file
SESSION_DRIVER=file

# Google reCAPTCHA v2 — these test keys always pass locally
NOCAPTCHA_SITEKEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
NOCAPTCHA_SECRET=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

### 4. Create the database, migrate, and seed

```bash
mysql -u root -e "CREATE DATABASE rentcar;"
php artisan migrate
php artisan db:seed
```

**Seeding is required.** It creates roles, permissions, and the built-in
accounts:

| Role        | Email                | Password |
| ----------- | -------------------- | -------- |
| Super admin | superadmin@gmail.com | 123456   |
| Owner       | owner@gmail.com      | 123456   |
| Manager     | manager@gmail.com    | 123456   |

Change passwords immediately on any non-local environment.

Create the storage symlink:

```bash
php artisan storage:link
```

> **Windows:** `storage:link` requires Developer Mode enabled or an elevated
> terminal. Re-run as Administrator if it fails silently.

### 4b. Fake data for local testing

`db:seed` already runs `DevDataSeeder`, which seeds every business table
with realistic test data scoped to the owner account. Nothing extra needed.

To reseed after wiping:

```bash
php artisan migrate:fresh --seed
```

To run only the fake-data seeder on an existing database (idempotent):

```bash
php artisan db:seed --class=DevDataSeeder
```

Seeded rows:

| Table | Rows | Details |
| --- | --- | --- |
| `vehicle_types` | 5 | SUV, Berline, Hatchback, Minivan, Cabriolet |
| `places` | 5 | Casablanca Airport, Marrakech Centre, Rabat Gare, Agadir Airport, Fès Médina |
| `vehicles` | 7 | RAV4, Duster, Clio, GLE, 208, T-Roc, Transit |
| `expense_types` | 6 | Carburant, Entretien, Assurance, Réparation, Nettoyage, Péage |
| `inspection_types` | 4 | Contrôle technique, Révision générale, Freins, Vidange |
| `reminder_types` | 4 | Renouvellement assurance, Vidange, CT, Révision |
| `addons` | 5 | GPS, Siège bébé, Conducteur additionnel, Assurance Premium, Wi-Fi |
| `options` | 5 | Climatisation, Bluetooth, Caméra de recul, Toit ouvrant, CarPlay |
| `bookings` | 8 | Mixed statuses, past and future dates |
| `expenses` | 7 | Realistic amounts linked to vehicles |
| `inspections` | 5 | Mix of pass/fail |
| `reminders` | 6 | Overdue, urgent, and upcoming |
| `rental_agreements` | 4 | completed, active, pending |
| `credits` | 3 | Linked to seeded drivers |

All rows are scoped to `parent_id = <owner id>` — visible when logged in as
`owner@gmail.com`.

> `DevDataSeeder` is dev-only and refuses to run `cleanStaleData()` in
> production (`APP_ENV=production`).

### 5. Run the app

In separate terminals:

```bash
php artisan serve        # http://localhost:8000
npm run dev              # Vite dev server with HMR
mailpit                  # http://localhost:8025 — catches outgoing mail
```

### 6. (Optional) Sail / Docker

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

---

## Common scripts

| Task | Command |
| --- | --- |
| Run PHP tests | `php artisan test` |
| Run a single test | `php artisan test --filter=BookingTest` |
| Run frontend tests | `npm test` (Vitest) |
| Build for production | `npm run build` |
| Tinker (REPL) | `php artisan tinker` |
| Clear all caches | `php artisan optimize:clear` |
| Re-run migrations from scratch | `php artisan migrate:fresh --seed` |
| Export translatable strings | `php artisan translatable:export <locale>` |
| Create Telescope tables (first run) | `php artisan telescope:migrate` |
| Clear Telescope entries | `php artisan telescope:clear` |

---

## Dev tools (Telescope + Debugbar)

### Laravel Telescope

Records HTTP requests, queries, jobs, mail, and more. Access at
`http://localhost:8000/telescope`.

First-time setup:

```bash
php artisan telescope:migrate
```

Toggle via `.env`:

```dotenv
TELESCOPE_ENABLED=true    # local dev
TELESCOPE_ENABLED=false   # production / staging — always set this
```

Telescope is filtered to `local` in `TelescopeServiceProvider`. In other
environments only exceptions, failed requests, and failed jobs are stored.
The `/telescope` panel is gated by the `viewTelescope` gate — add admin
emails there for production access.

### Laravel Debugbar

Injects a toolbar into HTML responses (queries, routes, memory). Disabled
automatically for API/JSON responses and non-debug environments.

```dotenv
DEBUGBAR_ENABLED=true    # local dev
DEBUGBAR_ENABLED=false   # production
```

Neither tool runs during `php artisan test` — `phpunit.xml` sets
`TELESCOPE_ENABLED=false` and `APP_ENV=testing`.

---

## Project structure

```
app/
  Clients/                   # per-client service providers and services
    DriveDesk/
  Contracts/                 # interfaces (PricingServiceContract, TvaServiceContract)
  Helper/helper.php          # global helper functions (autoloaded)
  Http/Controllers/          # 30 domain controllers + Auth/
  Models/                    # 30 Eloquent models
  Services/                  # TvaRenumberService
  Mail/                      # mailables
  Providers/
config/
  clients/                   # per-client config (_default.php, drivedesk.php)
  features.php               # global feature flag defaults
database/
  migrations/
  factories/, seeders/
lang/                        # 14 locales + matching <locale>.json files
resources/
  js/
    Pages/                   # Inertia/React page components
    components/
      ui/                    # shadcn/ui components (owned by the project)
    Layouts/
routes/
  web.php                    # main user/admin routes
  api.php                    # API routes (Sanctum)
  auth.php                   # Breeze auth scaffold
tests/
  Feature/
  Unit/
```

---

## Locales

14 locales: `ar`, `da`, `de`, `en`, `es`, `fr`, `it`, `ja`, `nl`, `pl`,
`pt`, `ru` plus corresponding `*.json` siblings. Both formats are used.
**Don't remove or rename existing keys.** Use
`kkomelin/laravel-translatable-string-exporter` to add new strings.

---

## Signatures and PDFs

Rental agreements are generated with `barryvdh/laravel-dompdf`. Signatures
are captured client-side with `react-signature-canvas` and stored via
`creagia/laravel-sign-pad`. Generated files land under `storage/app/` —
run `php artisan storage:link` if they don't appear.

---

## reCAPTCHA

`anhskohbo/no-captcha` (reCAPTCHA v2). Google's test keys
(`6LeIxAcTAAAAA...`) make reCAPTCHA always pass — use them locally.
Ask the team lead for production keys.

---

## Monitoring

Error monitoring via [Sentry](https://sentry.io). Ask the project owner
for access to the **rentcar** Sentry project.

Every event carries:

| Tag | Source |
| --- | --- |
| `environment` | `SENTRY_ENVIRONMENT` — e.g. `production-drivedesk`, `staging-drivedesk`, `ci` |
| `release` | `SENTRY_RELEASE` — set to `$GITHUB_SHA` in CI/CD |
| `user` | Authenticated user ID/email, captured automatically |

### Triage flow

1. Open the Sentry issue → read breadcrumbs and stack trace.
2. Assign yourself.
3. Fix in a branch; reference the issue in the commit:
   ```
   fix(BAN-N): <summary>

   Fixes SENTRY-RENTCAR-42
   ```
4. After deploy, mark the Sentry issue **Resolved**.

### Silencing Sentry locally

Leave `SENTRY_LARAVEL_DSN` empty in `.env` — the SDK no-ops when blank.

Smoke-test the integration locally (requires auth, local env only):

```
GET /sentry-test
```

---

## Multi-client architecture

The codebase serves multiple agencies from one deployment per client.
`APP_CLIENT` selects the active client. Adding a new client requires:

1. `config/clients/<newclient>.php` — feature flags, locale, branding seed, terms
2. `app/Clients/<NewClient>/` — service provider + custom service implementations
3. A branding seed row
4. A CI matrix entry
5. GitHub Environments (`staging-<newclient>`, `production-<newclient>`)

See `docs/client-configurability.md` for the full architecture.

---

## Deployment checklist

Each environment runs one client at a deploy-pinned tag (see
`CLAUDE.md` §10.3). Run these on every deploy of a tag to a
`production-<client>` / `staging-<client>` environment:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link            # see note below
php artisan optimize                # config/route/view cache
# restart queue workers / php-fpm
```

- **`php artisan storage:link` must be run once per environment.** Stored
  PDFs, signatures, and branding images are served through the
  `public/storage → storage/app/public` symlink; without it those URLs
  404 (broken-image previews). The symlink is **not** committed
  (`/public/storage` is gitignored) precisely because it is per-machine —
  never check it in. The command is idempotent, so re-running it on each
  deploy is safe.
- On a host where `storage/` is freshly created, also run the storage
  directory init from [Troubleshooting](#storage-directory-initialisation-fresh-clone)
  before `storage:link`.

---

## Troubleshooting

| Symptom | Likely fix |
| --- | --- |
| `Class "App\\Helper\\..." not found` | `composer dump-autoload` |
| Blank page after install | `php artisan key:generate`, check `APP_DEBUG=true` |
| 419 on POST forms | Clear browser cookies for `localhost` (CSRF/session) |
| Stored PDFs / signatures not displaying | `php artisan storage:link` |
| Vite assets not found | `npm run dev` not running, or wrong `APP_URL` |
| reCAPTCHA always fails locally | Use Google's test keys (see above) |
| Migrations error on `enum` change | `doctrine/dbal` is already installed |
| `file_put_contents(…sessions/…): Failed to open stream` | Run storage-init commands below |

### Storage directory initialisation (fresh clone)

`/storage` is gitignored — run after every fresh clone:

```bash
mkdir -p storage/framework/{sessions,views,testing,cache/data} \
         storage/logs \
         storage/app/public \
         storage/upload/{document,expense,logo,payment_receipt,picture} \
         storage/uploads/profile

chmod -R 777 storage/
php artisan storage:link
```

Required before the `/install/permissions` wizard step will pass.

---

## License

Proprietary. All rights reserved by the project owner.
