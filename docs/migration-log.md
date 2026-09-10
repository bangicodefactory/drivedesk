# Migration Log

Records manual smoke-test outcomes at each phase gate.
Required fields per entry: date, commit hash, tester, outcome, notes.

---

## Phase 1 — Full feature test coverage

### Automated coverage gate

| Date | Commit | Coverage (app/Http/Controllers/) | Result |
|------|--------|----------------------------------|--------|
| —    | —      | Pending CI run                   | —      |

### Gateway sandbox smoke tests — nothing to run

CLAUDE.md §4 requires money flows to be smoke-tested in a sandbox at each phase
boundary. **This repository has no money flow to test**, and the checklists that
stood here — Stripe and PayPal sandbox runs, every row "Pending" — described
integrations that never existed:

- no `stripe-php`, `srmklive/paypal` or Flutterwave package in `composer.json`;
- no checkout route, controller, callback or webhook;
- the credential forms that implied otherwise were removed in BAN-335, and the
  `paypal` / `stripe` feature flags in BAN-336 (declared `true`, read by
  nothing);
- half those rows tested `subscriptions`, a capability deleted in BAN-199.

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

### Stripe sandbox smoke tests

> Stripe/PayPal checkout is gated behind `feature('subscriptions')`, which is **disabled** for `directonderweg` (BAN-NEW-2). All subscription payment routes return 404 for this deployment — N/A for this client.

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

## Phase 5 — Inertia + React port

*(To be filled after Phase 5 work completes.)*

---

## How to record an entry

1. Run the manual smoke test against the relevant sandbox.
2. Fill in the date (`YYYY-MM-DD`), the short commit hash (`git rev-parse --short HEAD`), your name in the Tester column, and the outcome (`Pass` / `Fail` / `Partial`).
3. Add any failure notes in the Notes column and open a bug ticket if the outcome is not `Pass`.
4. Commit the updated log as `docs(BAN-XX): log Phase N smoke-test results`.
