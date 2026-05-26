# Migration Plan — Laravel 12 + Inertia/React

Owner: Ahmed
Branch: `feat/modernization` (off `dev`)
Last updated: 2026-05-15

This is the roadmap. Each phase has an **entry gate** (must be true to
start) and an **exit gate** (must be true to consider done and move
on). Phases are intentionally narrow so the diff is reviewable and a
regression is bisectable.

**Companion docs:**

- `CLAUDE.md` — rules of engagement
- `docs/test-plan.md` — test coverage strategy
- `docs/perf-audit-plan.md` — performance audit methodology
- `docs/client-configurability.md` — multi-client architecture (folded
  into Phase 0 below)

---

## Phase 0 — Safety net

Goal: we can break things and notice.

**Entry gate**

- `feat/modernization` branch exists, branched off `dev`.
- `CLAUDE.md` and `README.md` are merged into the branch.

**Work**

1. Set up local dev per `README.md` end-to-end on a clean machine
   (or VM/Sail). Capture any drift between the docs and reality.
2. Install Telescope (`laravel/telescope`) and Debugbar
   (`barryvdh/laravel-debugbar`) as `--dev` deps.
3. Enable MySQL slow-query log (>200ms) for the dev DB.
4. Add a one-page CONTRIBUTING note covering branch flow + test
   discipline (or fold into `CLAUDE.md`).
5. Run the perf audit (see `docs/perf-audit-plan.md`) and produce
   `docs/perf-audit.md`. **Do not fix anything yet** — this is the
   baseline.
6. Build the **test catalogue**: for each of the 29 domain
   controllers, list every route → method → required permission →
   required input. This is the checklist Phase 1 burns down.
7. **Stand up the multi-client skeleton** (see
   `docs/client-configurability.md`). Specifically:
   - Add `APP_CLIENT` to `.env.example`, default `directonderweg`.
   - Add `config('app.client')` entry in `config/app.php`.
   - Create `config/clients/_default.php` and
     `config/clients/directonderweg.php` (reproducing today's behavior
     — every feature on, every binding empty unless variant logic
     already exists).
   - Create `config/features.php`.
   - Add `App\Providers\ClientServiceProvider` and register it in
     `config/app.php`.
   - Create `app/Clients/DirectOnderweg/` namespace with an empty
     `DirectOnderwegServiceProvider`.
   - Add the `feature()` helper in `app/Helper/helper.php`.
   - Add the `RequireFeature` middleware (alias `feature`) — unused
     for now but ready to be applied.
   - Add `Tests\Concerns\WithClient` trait.
   - Verify: `php artisan test` still green, no behavior change.

**Exit gate**

- `docs/perf-audit.md` exists with at least the dashboard, booking
  list, vehicle list, and rental agreement generation profiled.
- Test catalogue committed under `docs/test-catalogue.md`.
- Multi-client skeleton in place with `APP_CLIENT=directonderweg`
  reproducing today's behavior exactly (test suite green).

---

## Phase 1 — Full feature test coverage

Goal: every controller endpoint has at least one happy-path and one
sad-path feature test, so we can refactor safely.

**Entry gate**

- Phase 0 exit gate met.
- Factories exist (or are written) for every model that controllers
  create/update.

**Work**

Burn down `docs/test-catalogue.md` in this priority order. Each
sub-phase is its own PR (or group of small PRs):

1. **Money & legally-binding flows** (most critical):
   `PaymentController`, `BookingController`, `RequestBookingController`,
   `CouponController`, `CreditController`, `TvaController`,
   `TvaRenumberController`, `SubscriptionController`,
   `RentalAgreementController`, `SignatureController`.
2. **Auth & permissions**: `Auth/*`, `UserController`,
   `RoleController`, `PermissionController`.
3. **Core domain CRUD**: `VehicleController`, `VehicleTypeController`,
   `DriverController`, `InspectionController`, `InspectionTypeController`,
   `OptionController`, `AddonController`, `ExpenseController`,
   `ExpenseTypeController`, `PlaceController`, `ReminderController`,
   `ReminderTypeController`, `NotificationController`.
4. **Settings & misc**: `SettingController`, `HomeController`.

For each endpoint, cover at minimum:

- Happy path (authenticated, allowed, valid input → expected response).
- Auth/permission denial (401/403).
- Validation failure (422 / redirect with errors).
- Not-found / wrong-tenant where applicable.

See `docs/test-plan.md` for conventions (factories, mocking, DB
isolation, etc.).

**Exit gate**

- `php artisan test` is green and `--coverage` reports ≥80%
  line coverage on `app/Http/Controllers/`.
- Payment/subscription routes return 404 for `directonderweg`
  (verified by `feature:subscriptions` gate tests). Stripe/PayPal
  sandbox testing is N/A — the feature is disabled for this client.

---

## Phase 2 — Laravel 10 → 11

Goal: cleanly land on Laravel 11 with no behavior change.

**Entry gate**

- Phase 1 exit gate met.
- PHP 8.3 installed locally and confirmed via `php -v`.

**Work**

1. Bump `composer.json`:
   - `"php": "^8.3"`
   - `"laravel/framework": "^11.0"`
   - Bump every package to its latest Laravel-11-compatible version
     (Sanctum, Breeze, Sail, Telescope, Debugbar, DomPDF, sign-pad,
     spatie/permission, no-captcha, srmklive/paypal, phpoffice/phpspreadsheet,
     laravelcollective/html — verify each release notes page).
2. `composer update` → resolve conflicts. If `laravelcollective/html`
   blocks the upgrade, port its usages to plain Blade and remove it.
3. Follow Laravel's [10 → 11 upgrade guide](https://laravel.com/docs/11.x/upgrade)
   in order. The structural changes (Kernel → bootstrap/app.php) are
   **optional** — only do them if you have a solid afternoon. Otherwise
   keep the legacy structure (Laravel 11 supports both).
4. Update PHPUnit to 11.x; fix any test breakage.
5. **Run the full suite. Bisect any new failure before moving on.**

**Exit gate**

- `composer show laravel/framework` → 11.x.
- `php artisan test` green.
- Manual smoke test of: login, create booking, generate rental
  agreement PDF, sign it. Stripe/PayPal N/A — `feature:subscriptions`
  disabled for `directonderweg`; routes verified to return 404.

---

## Phase 3 — Laravel 11 → 12

Goal: same idea, one step further.

**Entry gate**

- Phase 2 exit gate met.
- All packages are on Laravel-11-compatible *and* have a
  Laravel-12-compatible version available.

**Work**

1. `"laravel/framework": "^12.0"` + matching package bumps.
2. Follow the [11 → 12 upgrade guide](https://laravel.com/docs/12.x/upgrade).
3. Re-run the suite. Re-run manual smoke tests (login, PDF,
   signature). Stripe/PayPal N/A — see Phase 2 exit gate note.

**Exit gate**

- `composer show laravel/framework` → 12.x.
- Suite green; login, PDF generation, and signature pad manually
  verified. Stripe/PayPal N/A for `directonderweg`.

---

## Phase 4 — Laravel Mix → Vite

Goal: modern build pipeline, prerequisite for the React port.

**Entry gate**

- Phase 3 exit gate met.
- Node 20+ installed locally.

**Work**

1. `npm install vite laravel-vite-plugin @vitejs/plugin-react --save-dev`.
2. Create `vite.config.js` mirroring the current Mix entry points.
3. Replace `mix(...)` Blade helpers with `@vite([...])`.
4. Delete `webpack.mix.js` and `laravel-mix` from `package.json`.
5. Bump Tailwind to the latest major (3 → 4) **only if** all our
   Tailwind plugins (`@tailwindcss/forms`, etc.) have v4-compatible
   releases. Otherwise stay on 3 and bump later.
6. Verify every page loads with the same CSS/JS bundle behavior.

**Exit gate**

- `npm run dev` and `npm run build` both succeed.
- No visible style regressions on the 10 most-trafficked pages
  (dashboard, vehicle list, booking list, booking create, rental
  agreement, settings, login, register, customer-facing landing,
  inspection form). Log this in `docs/migration-log.md`.

---

## Phase 5 — Introduce Inertia.js + React shell

Goal: the app can serve React pages, even though most are still Blade.

**Entry gate**

- Phase 4 exit gate met.

**Work**

1. `composer require inertiajs/inertia-laravel`.
2. `npm install @inertiajs/react react react-dom`.
3. Publish + edit `app.blade.php` to become the Inertia root view.
4. Set up `resources/js/app.jsx`, `resources/js/Pages/` directory.
5. Install `tightenco/ziggy` so `route()` works in JS.
6. Migrate **one** trivial page (e.g. `/dashboard`) to Inertia/React as
   the proof-of-concept. Keep the Blade version available behind a
   feature flag (`INERTIA_ENABLED=true`) until the smoke test passes.
7. Define the shared-props contract (translations, current user,
   permissions, **features (from `config('client.features')`)**,
   **client branding** (from `Setting`), flash messages) — document
   it in `docs/inertia-shared-props.md`. See
   `docs/client-configurability.md` for the multi-client surface
   the props need to expose.

**Exit gate**

- `/dashboard` renders the React version when `INERTIA_ENABLED=true`,
  the Blade version when false, and both pass the existing feature
  tests.
- Shared-props contract documented.

---

## Phase 6 — Port pages to Inertia/React

Goal: every Blade view becomes a React page, in priority order.

**Entry gate**

- Phase 5 exit gate met.
- The replacement signature component is chosen and tested against
  `creagia/laravel-sign-pad` (round-trip a signature in tests).

**Work — port in this order, one PR per group:**

1. Auth pages (login, register, password reset, email verify).
2. Settings & user management (low complexity, high familiarity).
3. Vehicle / vehicle type / driver / place / option / addon CRUD.
4. Booking flow (validation, rental agreement generation, signature
   pad). Stripe/PayPal checkout pages are deleted in Phase 7 — do
   not port them.
5. Inspections, expenses, reminders.
6. TVA / TVA renumber (complex business rules — re-read the tests).
7. Rental agreement viewer/signer.
8. Dashboard widgets.
9. Customer-facing pages (public booking flow). The subscription
   landing/checkout pages are deleted in Phase 7 — do not port them.

For each group, the PR:

- Adds the React Page component under `resources/js/Pages/...`.
- Switches the controller to return `Inertia::render(...)`.
- Adds React-side tests (Vitest + React Testing Library) for any
  non-trivial component logic.
- Verifies the existing PHP feature tests still pass (they should —
  Inertia returns JSON when the `X-Inertia` header is set, which the
  tests can assert via `Inertia::assertPropValue(...)`).
- **Removes the corresponding Blade file** only after manual smoke
  test in a separate cleanup commit on the same branch.

**Exit gate**

- `resources/views/` only contains `app.blade.php` (the Inertia root)
  and any email/PDF templates that legitimately stay as Blade.
- `resources/js/` no longer imports jQuery or Alpine.
- All feature tests green; React component test suite (Vitest) green.

---

## Phase 7 — Cleanup and performance fixes

Goal: address the audit; tidy up.

**Entry gate**

- Phase 6 exit gate met.

**Work**

0. **Delete SaaS payment/subscription dead code** (BAN-NEW-4):
   - Delete `app/Http/Controllers/PaymentController.php`,
     `SubscriptionController.php`.
   - Delete `app/Models/Subscription.php`, `PackageTransaction.php`,
     `Coupon.php`.
   - Delete `resources/views/subscription/` and
     `resources/views/settings/payment.blade.php`.
   - Delete `config/paypal.php`.
   - Remove `srmklive/paypal`, `stripe/stripe-php`,
     `mashape/unirest-php` from `composer.json`.
   - Remove subscription/coupon permission rows from seeder.
   - Leave the DB tables in place; drop them in a post-migration
     schema cleanup PR after Phase 8.
1. Re-run the perf audit. Compare against the baseline — some issues
   will be fixed for free by Vite + React, others will still be there.
2. Pick the top 5 findings from `docs/perf-audit.md` and address them
   in one PR each:
   - Add eager loading where N+1s were measured.
   - Add indexes where slow queries justified them.
   - Add response caching / fragment caching where applicable.
   - Convert sync queue work to a real queue (Redis) for slow-but-not-
     user-facing operations (PDF generation, mail).
3. Remove `laravel-mix`, leftover Alpine deps, and other dead packages.
4. Update `README.md` to reflect the post-migration stack only.
5. Drop the `INERTIA_ENABLED` feature flag.

**Exit gate**

- Baseline perf metrics (p50/p95 of the 10 audited endpoints) have
  improved measurably vs. Phase 0 baseline. Logged in `docs/perf-audit.md`.
- Feature flag removed.
- README points only at the new stack.

---

## Phase 8 — Merge back to `dev`

Goal: ship.

**Entry gate**

- Phase 7 exit gate met.
- Stakeholders signed off on a staging environment running the
  migration branch.

**Work**

1. Rebase `feat/modernization` on the latest `dev`. Resolve conflicts
   in tight, reviewable chunks.
2. Run the full suite + manual smoke tests on the rebased branch.
3. Open the PR to `dev`. Reviewer cadence: per-phase folders, not
   one giant blob.
4. After merge to `dev`, monitor logs and Telescope for a week before
   the client picks up the change.

**Exit gate**

- `dev` ships the modernized app to the client without a P1 bug in
  the first week.

---

## Rollback strategy

- Every phase commits incrementally and **does not squash**, so
  `git bisect` can localize a regression to a single commit.
- Non-payment manual smoke tests (login, booking, PDF, signature)
  run at every phase exit gate (Phases 2, 3, 4, 6). Stripe/PayPal
  sandbox tests are N/A for `directonderweg` — the subscription
  feature is disabled and those routes return 404. If a smoke test
  fails, the phase rolls back to the previous gate's commit, not
  forward.
- Database schema is frozen, so rollback is just `git revert` +
  redeploy — no down-migrations to plan.

---

## Migration log

Append entries to `docs/migration-log.md` (created on demand) at every
phase gate, with date, commit hash, smoke-test results, and any
deviations from this plan. Future-you will thank present-you.
