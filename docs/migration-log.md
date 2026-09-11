# Migration Log

Records manual smoke-test outcomes at each phase gate.
Required fields per entry: date, commit hash, tester, outcome, notes.

---

## Phase 1 — Full feature test coverage

### Automated coverage gate

| Date | Commit | Coverage (app/Http/Controllers/) | Result |
|------|--------|----------------------------------|--------|
| —    | —      | Pending CI run                   | —      |

### Gateway sandbox smoke tests — nothing to run *today*

CLAUDE.md §4 requires money flows to be smoke-tested in a sandbox at each phase
boundary. **This repository has no gateway money flow to test**, because the one
it had was deleted — not because it never existed.

*(Corrected 2026-09-11, twice over. This section first said the checklists
"described integrations that never existed", and a first pass at correcting the
Phase 3 copy below repeated that. Git says otherwise, and a reviewer caught
it.)*

What existed, until June 2026:

- `stripe/stripe-php` `^16.0` and `srmklive/paypal` `~3.0.0` in
  `composer.json`, from the first commit until `4d9144ef`
  (*chore(BAN-199): remove billing Composer packages*, **2026-06-02**);
- `PaymentController`, `SubscriptionController`, `config/paypal.php`, five
  subscription Blade views and the `subscriptionPaymentSettings()` helper,
  until `135ea514` (*chore: remove Stripe/PayPal subscription billing flow*,
  **2026-06-01**);
- real checkout routes **with callbacks** — `POST subscription/{id}/stripe/payment`,
  `POST subscription/{id}/paypal` + `GET subscription/{id}/paypal/{status}`,
  `POST subscription/{id}/flutterwave` + `GET subscription/flutterwave/{id}/{txref}`
  — all three groups behind `feature:subscriptions`;
- credentials read from the `Setting` rows: `subscriptionPaymentSettings()`
  fed `paypal_client_id` / `paypal_secret_key` into `config('paypal.*')` at
  request time. So the forms removed in BAN-335 were that flow's real
  credential store, not decorative — BAN-335's "no reader of any credential
  key" was true *as of BAN-335*, the reader having been deleted three months
  earlier.

Two things follow, and both matter when reading the tables in later phases:

1. **It was never a rental money flow.** It billed the agency for its own
   DriveDesk subscription — packages, coupons, subscription plans. A customer
   paying for a car never touched it. The `coupons`, `coupon_histories`,
   `subscriptions` and `package_transactions` tables the roadmap lists as
   "schema-only" are this flow's residue.
2. **The "Pending" / N/A rows were accurate when written.** `subscriptions`
   was off for `directonderweg`, so the routes 404'd for that deployment —
   exactly as `docs/migration-plan.md:157` records. They are not evidence of a
   checklist invented against a phantom.

What is gone, and when: the flow (`135ea514`, 2026-06-01), its packages
(`4d9144ef` / BAN-199, 2026-06-02), the orphaned credential forms (BAN-335) and
the `paypal` / `stripe` flags that by then read nothing (BAN-336).

Do not treat their absence as an outstanding gate. What *is* real is the cash
path — recording a payment, the 5 000 MAD split and the factures it emits —
covered automatically by `tests/Unit/Services/CashPaymentSplitterTest.php`,
`ClientFeatureMatrixTest::test_a_cash_payment_over_the_ceiling_splits_for_drivedesk`
and the payment tests in `BookingControllerTest`. Verify it by hand at a phase
boundary the same way, over the ceiling, and record the run here.

When a gateway is genuinely integrated (CMI is the candidate; see
`booking_payment` in `docs/inertia-shared-props.md`), its sandbox checklist
belongs here, written against that integration rather than inherited from this
one.

---

## Phase 2 — Laravel 10 → 11

### Automated test suite

| Date | Commit | Tests | Coverage (Controllers) | Result |
|------|--------|-------|------------------------|--------|
| 2026-05-26 | 412d41da | 459 passed (980 assertions) | ≥50% (CI gate) | Pass |

Phase 2 changes shipped: `laravelcollective/html` removed (BAN-37), Laravel framework `^11.0` + PHPUnit `^11.0` + all compatible deps (BAN-37), `$routeMiddleware` → `$middlewareAliases` + `$dates` removal (BAN-39).

### Manual smoke tests

> The gateway sandbox tables that stood here are gone — see Phase 1 for why.
> Three of their rows had nothing to do with a gateway: they were filed under a
> "Stripe" heading because that heading happened to exist. Those are real and
> still pending, so they are kept, correctly named.

| Date | Commit | Tester | Flow | Outcome | Notes |
|------|--------|--------|------|---------|-------|
| — | — | — | Login → create booking | Pending | — |
| — | — | — | Generate rental-agreement PDF | Pending | — |
| — | — | — | Sign rental agreement (signature pad) | Pending | — |
| — | — | — | Re-download signed PDF | Pending | — |

---

## Phase 3 — Laravel 11 → 12

### Automated test suite

| Date | Commit | Tests | Result |
|------|--------|-------|--------|
| 2026-05-26 | 23608621 | 462 passed (983 assertions) | Pass |

Phase 3 changes shipped: Laravel `^12.0` + PHPUnit `^11.0` + all compatible deps (BAN-42), `previous_keys` + env-driven `maintenance` config (BAN-43). Suite green with no failures to bisect (BAN-44).

### Subscription-billing sandbox smoke tests — accurate then, moot now

> Stripe/PayPal checkout is gated behind `feature('subscriptions')`, which is
> **disabled** for `directonderweg` (BAN-NEW-2). All subscription payment routes
> return 404 for this deployment — N/A for this client.
>
> **Still-true note added 2026-09-11.** The sentence above was correct on
> 2026-05-26 and is kept verbatim: the routes existed and the flag really was
> off for that deployment (`docs/migration-plan.md:157` records the same 404
> check). What has changed is everything around it. The flow was deleted on
> 2026-06-01 (`135ea514`) and its packages on 2026-06-02 (`4d9144ef`, BAN-199);
> `subscriptions` itself was retired in BAN-318; and `directonderweg` moved to
> its own repo (`bangicodefactory/rentcar`) in the 2026-08-28 split. So the rows
> below are a valid record of a check against code this repository no longer
> contains — see Phase 1 for the full accounting, and note that this was SaaS
> billing (the agency paying for DriveDesk), never a customer paying for a car.
>
> *An earlier revision of this note claimed the premise was false — that no
> checkout had ever existed to gate. That was wrong, and git disproves it in
> one command (`git log -S 'stripe/stripe-php' -- composer.json`). Corrected
> before merge.*

| Date | Commit | Tester | Flow | Outcome | Notes |
|------|--------|--------|------|---------|-------|
| 2026-05-26 | 23608621 | Ahmed CHIOUA | Subscription checkout (Stripe) | N/A | `feature:subscriptions` disabled for directonderweg |
| 2026-05-26 | 23608621 | Ahmed CHIOUA | Subscription checkout (PayPal) | N/A | `feature:subscriptions` disabled for directonderweg |

### Manual smoke tests (non-payment flows)

| Date | Commit | Tester | Flow | Outcome | Notes |
|------|--------|--------|------|---------|-------|
| 2026-05-26 | 5b53ea4c | Ahmed CHIOUA | Login → dashboard | Pass | Logged in as owner@gmail.com, dashboard rendered correctly |
| 2026-05-26 | 5b53ea4c | Ahmed CHIOUA | Create booking + generate rental-agreement PDF | Pass | Booking #BOK-0001 created; Rental Agreement #RAG-0001 created (Active); Print button triggers browser PDF |
| 2026-05-26 | 5b53ea4c | Ahmed CHIOUA | Sign rental agreement (signature pad) | Pass | Signature created for driver (Ahmed Benali) via `/signature/create`; embedded as base64 PNG in agreement view |
| 2026-05-26 | 5b53ea4c | Ahmed CHIOUA | Re-download signed PDF | Pass | Rental Agreement #RAG-0001 show page renders with driver signature embedded; printable as PDF |

---

## Phase 4 — Laravel Mix → Vite

### BAN-50: Visual smoke test — 10 top pages on Vite build

Build: `npm run build` → `public/build/assets/app-NHXksaQF.css` (19.35 kB), `app-CvF1sPo4.js` (88.02 kB)

| Date | Commit | Tester | Page | URL | Outcome | Notes |
|------|--------|--------|------|-----|---------|-------|
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Login | `/login` | Pass | Auth layout, form fields, split panel all render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Register | `/register` | Pass | Registration form, split panel render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Dashboard | `/home` | Pass | Sidebar, stat cards, chart, notifications all render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Vehicle list | `/vehicle` | Pass | DataTable, header, Create button render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Booking list | `/booking` | Pass | DataTable with action buttons render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Booking create | `/booking/create` | Pass | Form fields, dropdowns, date pickers, Create button render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Rental agreement | `/rental-agreement` | Pass | DataTable, Create Agreement button render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Settings | `/settings/general` | Pass | Settings form, file inputs, Save button render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Customer landing | `/landing` | Pass | Hero section, navigation, header render correctly |
| 2026-05-27 | 4a9b6733 | Ahmed CHIOUA | Inspection | `/inspection` | Pass | DataTable with export buttons (Print/Excel/PDF/CSV/Copy) render correctly |

**No visible regressions on any of the 10 pages.** CSS output identical to pre-migration baseline (19.35 kB gzip: 4.57 kB). Phase 4 exit gate: **PASSED**.

---

## Phase 5 — Introduce Inertia.js + React shell

**Shipped. No manual gate run is recorded here** — the shell landed page by
page rather than at a single boundary, and nobody logged a run. What can be
stated from the code (checked 2026-09-11): the shell is in place
(`resources/js/app.jsx`, `AdminLayout`, `resources/js/Pages/**`), and the
suites that cover it run in CI per client.

This section used to read "*(To be filled after Phase 5 work completes.)*",
which implied a pending run. Nothing is pending: the entry is simply absent. If
a Phase 5 boundary check is still wanted, it has to be run now, against current
`dev`, and recorded here.

---

## Phase 6 — Port pages to Inertia/React

**Substantially shipped; two of the gate's three conditions are met.**
`docs/migration-plan.md:304-308` sets all three — and an earlier revision of
this section quoted only the first, which both understated progress and left
the jQuery/Alpine condition unrecorded:

| Exit-gate condition | Status (2026-09-11) |
|------|--------|
| `resources/views/` only holds `app.blade.php` + email/PDF | **Not met** — measured below |
| `resources/js/` no longer imports jQuery or Alpine | **Met** — `grep -rn "jquery\|jQuery\|alpinejs\|Alpine" resources/js/` returns nothing, and neither appears in `package.json`. This is CLAUDE.md §5's central frontend constraint, so it is worth recording as checked rather than assumed |
| Feature tests green; Vitest green | **Met** — 1436 passed / 6047 assertions locally, and the CI matrix runs per client |

The unmet one, re-derived from `return view(` / `Route::view(`:

| Measure | Value |
|------|--------|
| Blade pages still routed | 8 — `tva/create`, `booking/payment`, `logged_history/index`, `user_permission/create`, `settings/testmail`, `reminder/days_remaining`, `auth/confirm-password`, `client/pages/search` |
| Unauthenticated Blade previews | 15 — the `ui-test/*` group, marked for deletion |
| `.blade.php` files under `resources/views/` | 112 (21 `vendor/`, 10 `errors/`, 9 `email/`, 1 `pdf/`, 1 `seo/`) |

The per-page list, the scaffolding those extend, and two corrections to an
earlier version of it (`booking_requests/*` was never a Blade page;
`booking/planning` is Inertia) are in `docs/product-roadmap.md` §1.

Product work has continued on top of the migration since — `dev` was promoted
to `main` on 2026-09-10 (PR #56) — so this gate is now a cleanup debt tracked
in the roadmap's Cleanup section, not a blocker on further phases.

### Cash path — the money flow that is real

Phase 1 explains why there is no gateway checklist to run. The cash path is the
money flow this product actually has, and CLAUDE.md §4 wants it verified by
hand at a phase boundary, over the 5 000 MAD ceiling. **No run is recorded
yet.** When one happens, record it here.

| Date | Commit | Tester | Flow | Outcome | Notes |
|------|--------|--------|------|---------|-------|
| — | — | — | Record a payment over the 5 000 MAD ceiling → factures emitted | — | Not yet run |

### A note on `booking_payment`

The flag is on for `drivedesk` since BAN-334, which can read as "card payments
are live". It is not a money flow: it shows one tile in the `/reserve` wizard
and records `booking_requests.payment_preference`. Nothing charges a card, and
`RouteIntegrityTest` fails any route placed behind `feature:booking_payment`
until a real callback exists. There is nothing here to smoke-test until
roadmap item 2.6 ships a gateway — at which point its checklist belongs in this
log, written against that integration.

---

## How to record an entry

1. Run the manual smoke test against the relevant sandbox.
2. Fill in the date (`YYYY-MM-DD`), the short commit hash (`git rev-parse --short HEAD`), your name in the Tester column, and the outcome (`Pass` / `Fail` / `Partial`).
3. Add any failure notes in the Notes column and open a bug ticket if the outcome is not `Pass`.
4. Commit the updated log as `docs(BAN-XX): log Phase N smoke-test results`.
