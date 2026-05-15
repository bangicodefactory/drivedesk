# Test Plan — Full Feature Coverage

Last updated: 2026-05-15
Owner: Ahmed

This plan describes the safety net we must build **before** migrating
anything. The goal is "no behavior change goes unnoticed" — not
"100% code coverage". Coverage is a means; the metric we actually
care about is **regressions caught per migration phase**.

---

## What we test

Three layers, in priority order:

1. **Feature tests** — for every controller endpoint. These are the
   primary safety net during the migration. Hit the real database,
   real validation, real middleware. Mock only external HTTP services
   (Stripe, PayPal, mail transports).
2. **Browser / smoke tests** — for the handful of flows that cross
   process boundaries (Stripe Checkout, PayPal redirect, PDF render,
   signature pad). Stay manual for now; automate later if cheap.
3. **Unit tests** — only for pure logic with branching: TVA calculation,
   coupon application, credit math, date/locale formatters. Don't waste
   time unit-testing thin controllers.

We are **not** writing tests for Blade views. They're being deleted in
Phase 6 — testing them would be a waste.

---

## Test conventions

### Base class

All feature tests extend `Tests\TestCase` and use `RefreshDatabase`:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingControllerTest extends \Tests\TestCase
{
    use RefreshDatabase;
    // ...
}
```

If `RefreshDatabase` proves too slow once the suite grows, switch to
`DatabaseTransactions` *for read-heavy tests only*, never for ones
that hit queued jobs or commit-aware code.

### Factories

Every model under `app/Models/` needs a factory. Many already exist;
audit and write missing ones during Phase 0. Factories must produce
**valid** records by default (no nullables filled with garbage). State
methods cover variants:

```php
User::factory()->admin()->create();
Vehicle::factory()->available()->forBranch($branch)->create();
Booking::factory()->paid()->withRentalAgreement()->create();
```

### Permissions

The app uses `spatie/laravel-permission`. Tests must:

- Seed roles/permissions in `setUp()` (or via a dedicated trait).
- Run each "permitted" assertion as a user with the exact role(s)
  the route requires.
- Run a corresponding "forbidden" assertion as a user without the
  permission, expecting 403.

A `Tests\Concerns\WithRolesAndPermissions` trait that wraps this
boilerplate will save a lot of duplication.

### External services — mock at the boundary

- **Stripe**: use Stripe's test mode against the `stripe-mock` server,
  or stub the SDK with `\Mockery` at the boundary
  (`StripeClient::create()` returns a fake Session). Don't mock our
  own controllers.
- **PayPal**: srmklive/paypal supports `setApiCredentials(['mode' => 'sandbox'])`
  — for tests, swap the client out via a binding in the test container.
- **Mail**: `Mail::fake()` in every test that sends mail. Assert
  on the mailable class + recipient + key body content.
- **HTTP outbound** (Pusher, Google reCAPTCHA verify, etc.):
  `Http::fake()` with explicit URL patterns.
- **Filesystem** (PDF / signature uploads): `Storage::fake('local')`,
  then `Storage::disk('local')->assertExists(...)`.

### What each endpoint test covers

For every route, write at minimum these scenarios:

| Scenario             | Asserts                                                      |
| -------------------- | ------------------------------------------------------------ |
| Happy path           | 200/201/302 + correct view name (Inertia component name post-Phase 5) + correct DB state + correct mails/jobs dispatched |
| Unauthenticated      | redirect to login (web) or 401 (api)                         |
| Unauthorized         | 403 if user is authenticated but lacks the required permission |
| Validation failure   | 422 (api) / redirect with errors (web), no DB change         |
| Not found            | 404 when the model id doesn't exist                          |
| Wrong tenant/owner   | 403 when the resource exists but belongs to someone else (where applicable) |

You don't need a separate test method per scenario in every case —
data providers / `@dataProvider` are fine — but every scenario must
be covered for every endpoint.

---

## Specific test groups

### Money & legally-binding flows (highest priority)

These get **the most thorough tests and a manual sandbox smoke test
after every phase gate.**

- **PaymentController** — create payment intent (Stripe), create order
  (PayPal), capture, refund, partial refund, idempotency.
- **BookingController** — create, update, cancel, complete; verify
  pricing math against `Addon`, `Option`, `Coupon`, `Credit`, `Tva`.
- **RequestBookingController** — guest booking flow, conversion to
  full booking, validation of dates and vehicle availability.
- **CouponController** — apply, stack, expire, invalid; verify
  `CouponHistory` writes.
- **CreditController** — issue, redeem, expire, refund-to-credit.
- **TvaController** + **TvaRenumberController** — exhaustive coverage
  with table-driven tests for the renumber algorithm. The `TvaRenumberService`
  is also a Unit test target — test it in isolation.
- **SubscriptionController** — start, upgrade, cancel, expiration.
- **RentalAgreementController** — generate PDF, regenerate (idempotent
  filename?), download, void.
- **SignatureController** — accept signature payload, store, attach to
  rental agreement, replay protection.

### Auth & permissions

- All `Auth/*` controllers from Breeze — the bundled tests already
  cover most of this. Extend if our app added custom logic.
- **UserController** — admin CRUD, role assignment, permission
  changes, password reset by admin, email verification flow.
- **RoleController** — create, attach permissions, delete.
- **PermissionController** — create, attach to role, delete; assert
  that orphaned permissions don't grant access.

### Core domain CRUD

These are mostly resource controllers. Test the standard 6 (index,
show, create, store, edit, update, destroy) per controller, with
the scenarios from the table above:

- VehicleController, VehicleTypeController, DriverController,
  InspectionController, InspectionTypeController, OptionController,
  AddonController, ExpenseController, ExpenseTypeController,
  PlaceController, ReminderController, ReminderTypeController,
  NotificationController.

### Settings & misc

- **SettingController** — store/update settings, verify the helper
  in `app/Helper/helper.php` reads them back correctly.
- **HomeController** — dashboard widgets return the expected shape.

### Excel import/export

The recent commits touch a `downloadTemplate` and `importExcel` flow.
Test that:

- The downloaded template matches a known fixture column-for-column.
- Importing the same template back round-trips without data loss.
- A malformed file fails gracefully with a 422 + user-readable error.

### PDF + signature round-trip (Phase 6 gate)

When the signature component is swapped from jq-signature to
`react-signature-canvas` (or whatever we pick), add a test that:

1. Submits a fixed base64 PNG signature payload.
2. Asserts it was stored with byte equality (or pixel-equivalent) to
   what `creagia/laravel-sign-pad` produces today.
3. Generates the rental-agreement PDF and asserts it embeds the
   signature image at the expected coordinates.

If we can't get byte equality, a visual-diff snapshot test (e.g. with
`spatie/pdf-to-image` + a perceptual diff) is the fallback.

---

## Coverage targets per phase

| Phase                | Coverage gate                                              |
| -------------------- | ---------------------------------------------------------- |
| Phase 1 (test backfill) | ≥80% lines on `app/Http/Controllers/`, 100% of money flows |
| Phase 2 (→ Laravel 11)  | Suite green, no coverage regression                        |
| Phase 3 (→ Laravel 12)  | Same                                                       |
| Phase 4 (Vite)          | Same (Vite doesn't change PHP)                             |
| Phase 5 (Inertia shell) | Same + at least 1 Inertia-specific test using `Inertia::assertPropValue` |
| Phase 6 (port pages)    | Per-PR: every ported page's existing feature tests still green + new React component tests (Vitest) for non-trivial component logic |
| Phase 7 (cleanup)       | Same                                                       |

We measure coverage with PHPUnit's built-in coverage (`--coverage-clover`)
and gate CI on it once Phase 1 lands.

---

## Test data isolation

- No real customer data in test fixtures.
- Local DB is a separate schema (`rentcar_test`), never
  reused for manual dev.
- `phpunit.xml` sets `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:`
  for speed *unless* a test specifically needs MySQL features (full-text,
  JSON columns with MySQL-specific operators). Use the `@requires`
  annotation to mark those.

---

## CI

Once the suite is meaningful (end of Phase 1):

- Run `php artisan test --coverage --min=80` on every PR to `feat/modernization`.
- Run `npm test` (Vitest) after Phase 5.
- Cache `vendor/` and `node_modules/`.
- Fail the build if `composer audit` reports a high-severity advisory.

---

## What we are explicitly NOT testing

- The web installer (`rachidlaasri/laravel-installer`) — it runs once
  per environment and is covered by the README install instructions.
- Blade view rendering details — those views are being deleted.
- Third-party packages — we trust `spatie/laravel-permission`, etc.
  We test *our use of them*, not their internals.
- Realtime broadcasting setup — currently `BROADCAST_DRIVER=log` in
  dev; if/when we enable Pusher in earnest, write tests then.
