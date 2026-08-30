# DriveDesk — Product roadmap (market research + app audit)

Date: 2026-08-29 · Owner: Ahmed · Status: proposed

This document is the outcome of three investigations run on 2026-08-29:

1. a **feature inventory** of this repository (routes, controllers, models,
   migrations, `config/features.php`, `config/clients/drivedesk.php`);
2. a **UI/UX audit** of `resources/js/` (layouts, pages, shadcn primitives,
   i18n/RTL, mobile, accessibility, tests);
3. **market research** on car-rental management software serving Morocco,
   the marketplaces that feed inbound demand, international benchmarks, and
   the Moroccan regulatory calendar.

It replaces nothing in `docs/migration-plan.md`; it sits after it. Every item
below is sized S / M / L and, where it is a variant, names the feature flag it
must ship behind (`config/features.php` + `config/clients/<client>.php`, see
`CLAUDE.md` §10).

---

## 1. Where DriveDesk stands today

The sales handbook (`docs/sales/training-en.html`, §9 "What you must not
promise") already lists what the marketing site overstates. This table is the
engineering view of the same question, reconciled with the code.

| Area | Status | Where |
| --- | --- | --- |
| Bookings, Excel import, bulk actions, select-all-matching | **Real** | `app/Http/Controllers/BookingController.php`, `resources/js/Pages/Booking/` |
| Planning board (vehicle × day Gantt) | **Real**, fragile | `Pages/Booking/Planning.jsx` loads a vendored 991 KB FullCalendar bundle (`public/js/index.global.js`) and uses `resourceTimeline*` (Premium) views |
| Fleet, vehicle types, options | **Real** | `VehicleController`, `Pages/Vehicle/` |
| Customers (drivers), blacklist | **Real** | `DriverController`, `driver_blacklists`, `components/BlacklistNotice.jsx` |
| Rental agreements + in-app e-signature | **Real** | `RentalAgreementController`, `creagia/laravel-sign-pad`, `components/SignaturePad.jsx` |
| Inspections (checklist, notes, odometer, cost, one file) | **Real**, no photos / no damage diagram | `InspectionController` |
| Expenses, reminders (hourly/daily scheduler), credits | **Real** | `ExpenseController`, `ReminderController`, `app/Console/Kernel.php`, `CreditController` |
| Traffic violations w/ plate → booking matcher | **Real** (flag `traffic_violations`) | `TrafficViolationController`, `app/Services/ViolationMatcher.php` |
| TVA invoices, PDF, monthly report, gap-free renumbering | **Real** | `TvaController`, `TvaRenumberController`, `resources/views/pdf/invoice1.blade.php` |
| Cash ceiling (CGI art. 193, 5 000 MAD) split into compliant receipts | **Real** (flag `cash_split`) | `app/Services/CashPaymentSplitter.php` |
| Invoice only once fully paid | **Real** (flag `invoice_on_full_payment`) | `BookingController::recordBookingPayment` |
| Roles / ~100 permissions, audit log | **Real** | spatie, `logged_histories`, `config/audit.php` |
| Per-tenant SMTP + 6 mailables, reCAPTCHA, Sentry | **Real** | `SettingController`, `app/Mail/` |
| Demo gateway + demo-request approval | **Real** (flag `demo_gateway`) | `Pages/Public/DemoGateway.jsx`, `DemoApprovalController` |
| SEO: sitemap, `llms.txt`, hreflang, locale-prefixed public URLs | **Real** | `SeoController`, `app/Support/{Seo,Locales}.php` |
| Locales en / fr / ar / ary with true RTL | **Real** | `app/Http/Middleware/SetLocale.php`, `resources/js/app.jsx` |
| Stripe / PayPal / Flutterwave | **Settings-only** — credential fields, no SDK, no checkout, no webhook | `Pages/Settings/Payment.jsx` |
| Subscriptions, packages | **Schema-only** — tables, no model/controller/route | `database/migrations/*subscriptions*`, `*package_transactions*` |
| Coupons | **Schema-only** | `*create_coupons_table*`, ~10 orphan keys in `resources/lang/en.json` |
| Multi-branch | **Flag-only** — `multi_branch` has no enforcement point; Places are pick-up points with a surcharge, not branches | `config/features.php` |
| SMS, WhatsApp | **Absent** (`ReminderController` has a commented `sendSMSNotification`) | — |
| Reminder e-mails | **Broken, silently** — `ReminderController` calls `Mail::send('emails.reminder_notification')` / `'emails.daily_reminder_summary'` and the daily summary is scheduled, but the views live under `resources/views/email/` (singular) and `daily_reminder_summary.blade.php` does not exist; the `try/catch` downgrades the exception to `Log::error` | `app/Http/Controllers/ReminderController.php:447,631`, `app/Console/Kernel.php` |
| Deposit / caution, franchise, km limit, late fee | **Absent** — mentioned in contract terms text only | `config/clients/drivedesk.php` `terms.rental_agreement` |
| Vehicle document expiry (assurance / vignette / visite technique) | **Absent** as fields — only `registration_expiry` exists | `vehicles` table |
| Weekly / monthly rate cards | **Absent** — daily rate + manual discount | `vehicles.daily_rate` |
| Damages / état des lieux with photos | **Absent** | — |
| Maps / geocoding, GPS | **Absent** | `places` are free-text addresses |
| Public API / mobile app / PWA | **Absent** — `routes/api.php` is the Sanctum stub | — |
| In-app notification centre | **Absent** | — |
| Accounting export, DGI e-invoicing (UBL 2.1 / CII) | **Absent** | — |

### Hygiene debt found on the way

- 7 of 13 feature flags have **no enforcement point**: `paypal`, `stripe`,
  `booking_payment`, `excel_import`, `multi_branch`, `tva_renumber`,
  `signatures`. Flipping them changes nothing. (`subscriptions` *is* read —
  seven `feature('subscriptions')` branches in the still-shipped Blade
  `admin/menu.blade.php` and `dashboard/super_admin.blade.php`, one of which
  also hides the Logged History menu entry — so it must not be deleted before
  that Blade is retired.)
- `app/Http/Controllers/HomeController.php` imports five classes that do not
  exist (`Contact`, `Fuel`, `NoticeBoard`, `Service`, `Support`).
- `routes/web.php`: `ui-test/*` (15 unauthenticated Blade previews) and
  `GET /hello` are marked "remove before production"; `POST /newsletter/subscribe`
  validates and discards the address.
- `tvas.company_name` defaults to `'DIRECT ONDERWEG'`; `settingsKeys()` default
  timezone is `Pacific/Tahiti`; the bank section asks for an Indian `IFSC` code
  instead of a RIB/IBAN.
- Ten dead locale bundles under `resources/lang/` (`danish`, `dutch`, …) that
  `SetLocale::SUPPORTED` never serves; `nl` is declared in
  `config/clients/drivedesk.php` but not servable.
- `composer.lock.backup` committed; product still named `rentcar` in
  `package.json`, `.env.example`, and the `CLAUDE.md` title.
- `docs/phase6-execution-plan.md` reports ~13 % of the Blade port done; the
  `resources/js/Pages/` tree shows it is essentially complete. The Phase 6 exit
  gate ("`resources/views/` only holds `app.blade.php` + email/PDF") is still not
  met. Still Blade and still routed: `tva/create`, `booking/payment`,
  `booking_requests/*`, `logged_history/*`, `user_permission/*`,
  `settings/testmail`, `reminder/days_remaining` (returned by
  `ReminderController.php:297`), `auth/confirm-password`, and the whole
  `client/**` storefront — plus the scaffolding they extend:
  `layouts/{app,auth,guest,landing}`, `admin/{menu,content,header,footer,head}`,
  `dashboard/{index,super_admin}`, `driver/new_create`,
  `reminder/_date_modal`, `tva/{days_remaining,_date_modal}`, `partials/alerts`.
  Roughly 30 files, not 7.
- Two vitest conventions coexist (`Pages/**/__tests__/` and
  `resources/js/tests/`), duplicating e.g. the Login test; no coverage threshold.

---

## 2. Competitive landscape

### 2.1 Moroccan rental-management SaaS (direct competitors)

The market is crowded and the price floor is low (117–300 DH/month). "Fleet +
contracts + invoices" is commoditised.

| Product | Positioning |
| --- | --- |
| [Rentyx](https://rentyx.ma) | Morocco-only, 2 500 MAD/yr unlimited fleet, plus a commission plan; heavy "Excel vs logiciel" content marketing |
| [GestLoc](https://gestloc.ma) | Most enterprise-shaped local player: drag-and-drop planning, multi-agency, bank integration, 10+ reports, 299 → 2 499 DH/mo |
| [CRSApp](https://www.crsapp.ma) | 85+ agencies; 699 DH / 6 months starter; sells 6- and 12-month plans only; has an "infractions" module |
| [GoRently](https://gorently.ma) | Founded 2024; AI-first: WhatsApp/web conversational booking, AI photo damage detection, OTA-ready REST API; 490 / 890 MAD/mo |
| [Locapp](https://locapp.ma) | Rabat, 70+ agencies; classic stack + strong Darija social proof; 7-day trial |
| [Loc.ma](https://www.loc.ma) | SaaS + a free storefront per agency (`agence.loc.ma`) with direct booking |
| [LocaFlotte](https://locaflotte.com) | 169 MAD/mo entry, FR/AR/EN/ES, Android app; publicly admits no Arabic PDF contracts and no booking module |
| Fleety, Fleetement, ISY Solutions | All-in-one; ISY headlines digital état des lieux + e-signature; Fleetement markets "reservations arriving via WhatsApp" |
| [MekLoc](https://mekloc.com), [NextFlotte](https://nextflotte.com), Agencar, GENIPARC / Genicars, Maroc Rent Solutions, GestFlotte, Loca-Smart | Long tail; NextFlotte runs the best research blog in the niche (caution guides, AI état des lieux) |
| [Qualitrace](https://qualitrace.ma), [Geo4tech](https://geo4tech.ma) | Rental software bundled with GPS/telematics |
| RAKIB, ProFleetPlus, VestraCar (Google Play) | Mobile-first agency managers: contract generation, check-in/out photo documentation, KPI dashboard |

### 2.2 Demand-side channels (integrate with, do not fight)

[KARVYX](https://karvyx.com) (new Moroccan marketplace, partner extranet),
[LocalRent](https://www.localrent.com/en/morocco/) (dominant cash-deposit
marketplace, 15–20 % prepay), [OneClickDrive.ma](https://www.oneclickdrive.ma),
Sogme, plus the European comparators (Carigami, HappyCar, Liligo, Skyscanner,
BSP-Auto, Rentcars). Avis.ma / Europcar / Hertz set the UX bar customers compare
against.

### 2.3 International benchmarks

| Product | What sets the bar |
| --- | --- |
| [RentSyst](https://rentsyst.com) | Inspection module with photos + notes, e-sign contracts, mileage/date-triggered workflows, GPS + payment APIs; €1.60/car/month |
| [HQ Rental Software](https://www.capterra.com/p/156984/HQ-Rental-Software/) | Reservations, rate rules, add-ons, maintenance, embeddable booking plugin, sales-agent channel |
| [Coastr](https://www.coastr.com) | Telematics, remote immobilisation, biometric verification, white-label portal |
| [Rent Centric](https://www.rentcentric.com) | Licence-barcode auto entry, image/video capture, e-sign, kiosks |
| [Booqable](https://booqable.com) | Website builder + embeddable booking widget, pricing rules, iOS/Android app; $29–149 |

---

## 3. Moroccan regulatory and market must-haves

| Requirement | What it means for the product | Sources |
| --- | --- | --- |
| **DGI e-facturation** (clearance model: invoice validated by the DGI platform *before* it reaches the customer; UBL 2.1 or CII only; qualified e-signature). Calendar: large IS companies 1 Jan 2026 → medium 1 Jul 2026 → **TPE (< 10 M DH) and auto-entrepreneurs 1 Jan 2027**. Everyone must be able to *receive* e-invoices from 2026. | DriveDesk's ICP lands in the Jan-2027 wave. UBL 2.1 export + ICE validation + a clearance adapter is a category-defining feature; no Moroccan rental SaaS advertises it. | [Upsilon](https://www.upsilon-consulting.com/facturation-electronique-maroc-2026/), [Hisab](https://hisab.ma/fr/docs/mandate-2026), [Experio](https://experio.ma/facturation-electronique-maroc-2026-guide-conformite/) |
| **Art. 145 CGI** — 12 mandatory invoice mentions; ICE is 15 digits and the DGI platform auto-rejects a missing/invalid one; TVA 20 % standard. | Validate ICE format on driver/company records and on `tvas`; keep the 12 mentions on `invoice1.blade.php`. | [C2M checklist](https://www.c2m.ma/mentions-obligatoires-sur-une-facture-au-maroc-la-checklist-complete-pour-eviter-le-rejet-dgi-en-2026/), [ClicPaie ICE](https://clicpaie.ma/blogs/ice-maroc/) |
| **Cahier des charges** for location sans chauffeur (in force 15 Apr 2024; compliance deadline end-2025, fleet standards for existing agencies until 2027): ≥ 7 vehicles, CNSS registration, registered office, qualified licence holder, 500 000 DH funds/bonds. | An "agency compliance file" (fleet count vs threshold, CNSS, licence, insurance policies, document expiry) — nobody offers it. | [Médias24](https://medias24.com/2025/04/12/nouveau-cahier-des-charges-pour-la-location-de-voitures-les-anciennes-agences-partiellement-exonerees/), [Le Matin](https://lematin.ma/nation/location-de-voitures-nouveau-delai-pour-appliquer-le-cahier-des-charges/277176), [Manis](https://manisconsulting.ma/guide/fr/cahier-charges-location-voitures/) |
| **Contract mandatory elements**: CIN, permis n° + category + issue date, vehicle + km de départ, exact dates, prix TTC, **caution** (amount, form, release), insurance company + policy n° + type + **franchise**, return conditions, liability, jurisdiction, dated signatures. | Caution, franchise, km départ/retour, policy n° must become fields, not prose in `terms.rental_agreement`. | [NextFlotte](https://nextflotte.com/blog/contrats-location-voiture-maroc-modeles-gratuits), [Clic1Car](https://clic1car.com/blog/assurance-franchise-caution-location-voiture-maroc/) |
| **Caution norms**: 8 000–15 000 MAD (SUV), up to 25 000 (premium); forms are pré-autorisation CB, chèque de garantie, espèces; many tourist offers are deposit-free. | Model caution as its own object: type, amount, hold date, release date, deductions. | [NextFlotte caution](https://nextflotte.com/blog/caution-depot-garantie-location-voiture-maroc), [Jacaranda](https://www.jacarandacar.com/blog/post/location-de-voiture-au-maroc-payer-carte-ou-esp%C3%A8ces) |
| **PV / amendes**: since 1 Jul 2011 agencies no longer settle fines for non-residents (collected at the border); for residents the agency receives the PV, may advance it, then re-invoices + a processing fee. | Extend `traffic_violations` with resident/non-resident routing and a re-invoice line. | [Aujourd'hui le Maroc](https://aujourdhui.ma/societe/infractions-au-code-de-la-route-les-agences-de-location-de-voitures-ne-payent-plus-pour-les-non-residents-80543) |
| **Payment gateways in MAD**: YouCan Pay 2.5 % + 3 MAD, no monthly fee, online onboarding; CMI 3 000–5 000 DH setup + 2.5–3.5 % + monthly; Payzone 2.5–3.5 %. | YouCan Pay is the low-friction default for deposit prepay and for DriveDesk's own billing; CMI for larger agencies. | [Digitoyou](https://digitoyou.com/blog/paiement-en-ligne-maroc-cmi-stripe-2026/), [Sinesi](https://www.sinesi.net/blog/paiement-en-ligne-au-maroc-cmi-cashplus-ou-payzone-le-comparatif) |
| **WhatsApp** is the #1 closing channel for independent agencies (ahead of e-mail): booking confirmation, contract + photos, pre-pickup reminder, incident reporting on the same thread. | Ship WhatsApp as a transactional channel, starting with `wa.me` deep links, later the Business API. | [Perfect Rental](https://perfectrental.ma/fr/blog/whatsapp-business-car-rental-agency) |
| **Digital état des lieux**: ~27 % fewer disputes; > 40 % of disputes concern end-of-rental damage and fail for lack of dated photos. | Guided photo capture (same angles at departure and return), timestamped signature, PDF to e-mail + WhatsApp. | [NextFlotte EDL](https://nextflotte.com/blog/etat-des-lieux-numerique-scan-ia-location-voiture), [Fleetee](https://www.fleetee.io/blog/etat-des-lieux-numerique) |
| **Market shape**: 11 246 agencies, 201 462 vehicles (2025); most agencies run < 7 vehicles; hubs Marrakech (least seasonal), Casablanca (CMN), Agadir, Tanger; MRE peak June–September. | ICP = a 3–10-vehicle, phone-first, one- or two-person agency with no accountant. | [Bladi.net](https://www.bladi.net/000-agences-marche-eclate-fragilites-location-voitures-maroc,118515.html), [NextFlotte 2026](https://nextflotte.com/blog/marche-location-voiture-maroc-2026-tendances) |

---

## 4. Feature gap matrix

Importance is for the Moroccan ICP. Effort: S ≤ 1 PR, M = 2–4 PRs, L = an
epic. "MA" = at least one Moroccan competitor ships it; "Intl" = the
international benchmarks ship it.

| Feature | MA | Intl | Importance | DriveDesk | Effort |
| --- | :-: | :-: | :-: | --- | :-: |
| Reservations + colour planning w/ drag-and-drop + conflict detection | ✅ | ✅ | High | Planning is read-only, no drag | M |
| Expiry alerts: assurance, vignette, visite technique | ✅ | 🟡 | High | Only `registration_expiry` | S |
| Caution / deposit lifecycle | 🟡 | 🟡 | High | Absent | S–M |
| Km limit + extra-km billing, franchise per booking | 🟡 | ✅ | High | Absent | S |
| État des lieux with photos, départ vs retour comparison | 🟡 | ✅ | High | Checklist + 1 file | M |
| E-signature on contract | 🟡 | ✅ | High | ✅ | — |
| Arabic UI + true RTL | 🟡 | ❌ | High | ✅ UI (list pages break, see §5) | S |
| Bilingual FR/AR contract & EDL **PDF** | ❌ | ❌ | High — clear gap | Absent (English terms, LTR PDF) | M |
| WhatsApp: send contract / EDL / reminders | 🟡 | ❌ | High | Absent | S (links) / M (API) |
| Mixed cash + virement + chèque + card on one contract | 🟡 | ❌ | High | ✅ partial payments, cash ceiling | — |
| PV register + resident/non-resident + re-invoice | 🟡 | 🟡 | High | ✅ register; routing/re-invoice absent | S |
| DGI e-facturation (UBL 2.1 / CII, ICE validation, clearance) | ❌ | ❌ | High — 2027 forcing function | Absent | M–L |
| Art. 145 invoice mentions incl. 15-digit ICE | 🟡 | ❌ | High | ✅ fields; no ICE validation | S |
| Weekly / monthly rate cards, seasonal grids | 🟡 | ✅ | Med-High | Daily only | S–M |
| Airport / hotel delivery & one-way fees | 🟡 | 🟡 | High | ✅ via Places surcharge | — |
| LLD / long-term with monthly invoicing | 🟡 | ✅ | Med-High | Absent | M |
| Maintenance / vidange scheduling | ✅ | ✅ | Med-High | ✅ via Reminders | — |
| Reports export (Excel/PDF), 10+ reports | ✅ | ✅ | High | TVA report + bulk PDF only | M |
| Multi-agency / multi-branch | ✅ | ✅ | High | Flag-only | L |
| Included storefront / embeddable booking widget | ✅ | ✅ | High | Blade storefront, off for drivedesk | M |
| OTA / marketplace connectivity (Karvyx, LocalRent, Booking) | 🟡 | ✅ | High | Absent | L |
| Online deposit prepay (YouCan Pay / CMI) | 🟡 | ✅ | Med | Absent | M |
| Field-agent mobile app / PWA | ✅ | ✅ | High | Responsive web only, weak on phones | M–L |
| GPS / telematics | 🟡 | ✅ | Med | Absent | L |
| Customer portal / self-service | ❌ | ✅ | Low-Med | Absent | L |
| Cahier des charges compliance file | ❌ | ❌ | Med — unique | Absent | S–M |
| Sub-rental between agencies | ❌ | ❌ | Med — unique | Absent | M |

---

## 5. UI/UX audit findings

Stack correction first: the frontend runs **Tailwind 3.4** (`tailwind.config.js`,
`postcss.config.js`, `@tailwind` directives in `resources/css/app.css`), not
Tailwind 4 as `CLAUDE.md` §1 targets. Any plan written against v4 (`@theme`,
CSS-first config) does not apply until that upgrade lands.

### What is strong

- Design tokens: complete light/dark HSL token set in `resources/css/app.css`;
  `Pages/Settings/Branding.jsx` derives foreground colours by iterating to WCAG
  4.5:1 contrast (mirrors `App\Support\ThemePalette`); `app.jsx:applyBranding`
  applies before first paint, no theme flash.
- `hooks/useZodForm.js` — RHF ↔ Inertia bridge with 422 mapping, cancel-safe.
- `ConfirmProvider` replaces `window.confirm`; `lib/nav.jsx` filters nav by
  permission **and** feature flag; `components/ui/date-picker.jsx` reuses the
  native picker.
- `Pages/Booking/Index.jsx` is the reference list: debounced server search,
  month filter, contextual bulk bar, select-all-matching with `AbortController`,
  filtered vs empty states.

### Gaps, ranked by leverage

1. **Form errors are never announced.** `components/ui/form.jsx` is dead code —
   no page imports it. 63 pages hand-roll `Label + Input + <p class="text-destructive">`;
   the only `aria-invalid` under `resources/js/` is inside that unused primitive —
   no page sets it.
2. **RTL breaks on every list page.** 189 physical-direction utilities
   (`text-right`, `ml-`/`mr-`/`pl-`/`pr-`) across 49 files; ~24 index pages
   override `TableHead`'s `text-start` with `text-right` on the actions column
   (`Pages/Vehicle/Index.jsx`, `Pages/Place/Index.jsx`, `Pages/Booking/Index.jsx`, …).
   Worst offenders: `Pages/Tva/Report.jsx` (30), `Pages/Booking/Show.jsx` (12) —
   both financial layouts where mirroring matters most.
3. `components/Pagination.jsx` hardcodes "Prev" / "Next" / "of", has no
   `<nav aria-label>`, no page links, no per-page selector (`per_page` appears
   nowhere).
4. **"System" theme is a lie.** `Pages/Settings/Branding.jsx` offers a System
   tile (zod enum accepts `systemmode`) but `app.jsx` maps anything ≠ `darkmode`
   to light and sets `enableSystem={false}`.
5. 16 index pages ship the whole dataset and filter client-side (`Users`,
   `Inspection`, `Reminder`, `Credit`, `Place`, `Addon`, `Option`, all `*Type`,
   `Roles`, `Notification`, `BookingRequest`); 7 are server-paginated.
6. No column sorting anywhere; bulk actions on 2 of 24 lists (`Booking`, `Tva`).
7. **Mobile is the weakest area.** 45 of 95 pages have zero responsive classes
   (all Auth, all Settings, 16 index pages). Tables are raw `<table>` with
   horizontal scroll and no card fallback; forms are `grid-cols-1` with no
   `md:grid-cols-2`.
8. 43 of 95 pages have no `<h1>` (every Create/Edit/most Show); no per-page
   `<Head>` title in the admin; no skip link and no `<main>` in `AdminLayout.jsx`.
9. `components/ui/searchable-select.jsx` is not an accessible combobox: no
   `listbox`/`option` roles, no arrow keys, no Escape; hardcoded English defaults.
10. `Pages/Booking/Planning.jsx` injects the FullCalendar script with no loading
    or error state, hardcoded `rgba(33,150,243,…)` colours (unreadable in dark
    mode), and uses **Premium** `resourceTimeline*` views — licence risk.
11. `Pages/Public/Landing.jsx` auto-advances a carousel every 5 s with no pause
    (WCAG 2.2.2) and unlabeled arrows; `Pages/Public/DemoGateway.jsx` bypasses
    tokens with 56 inline styles and labels without `htmlFor`.
12. Two form conventions: 45 pages on `useZodForm`, 9 on raw Inertia `useForm` —
    and those 9 are the most complex forms (`Booking/{Create,Edit}`,
    `RentalAgreement/{Create,Edit}`, `Credit/*`, `Notification/*`, `Tva/Edit`),
    i.e. the ones without client-side validation.
13. Loading states: `Skeleton` used once (`dashboard/StatCard.jsx`); submit
    buttons disable but show no spinner. (Inertia's default top progress bar is
    active — `app.jsx` passes no `progress` option — so that part is fine.)
14. Only `en`/`fr`/`ar` are exposed in `AdminLayout.jsx`; the `ar.json` bundle
    (89 KB) is inlined into every page's props.

### UX conventions of the best apps in this market

- Dashboard = "départs du jour / retours du jour" lists + alert stack
  (assurance / vignette / visite technique / vidange / retours en retard /
  cautions non restituées). DriveDesk's dashboard already has the KPI row,
  immediate actions and a 7-day fleet strip; it lacks the two "today" lists.
- Planning = vehicle rows × day columns, colour by status, drag to extend/move
  with conflict detection, quick-add from an empty cell.
- Contract creation = a wizard (client → véhicule → dates & tarif → options /
  franchise / km → caution → signature) with reuse-first client lookup by
  CIN / phone and inline CIN / permis capture.
- Field agent = a phone flow for the état des lieux: fixed photo angles,
  offline-tolerant, one-thumb.
- Localisation = FR / AR / EN switcher, RTL down to the PDF, MAD, `+212`
  normalisation. Support via WhatsApp 7/7 is sold as a feature.

---

## 6. Prioritized backlog

Rules for every item: additive, reversible migrations only; default behaviour
unchanged for the existing client; variant behaviour behind a flag; tests first.

### Tranche 0 — foundation

Items 0.1 (first slice) to 0.4 are implemented on branch
`ux/a11y-rtl-foundation` (PR #4, commits BAN-271/273/274/275). The rest of
0.1 and items 0.5–0.7 are follow-up PRs.

| # | Item | Effort |
| --- | --- | :-: |
| 0.1 | Accessible field errors: `FieldError` + `fieldA11y()` helpers (`resources/js/components/FieldError.jsx`, `resources/js/lib/fieldA11y.js`), adopted on a first slice in PR #4, then every remaining form page in module-sized commits | S + M |
| 0.2 | RTL sweep: physical → logical utilities (`text-end`, `ms-`/`me-`/`ps-`/`pe-`, `start-`/`end-`) with a guard test | S |
| 0.3 | `Pagination.jsx`: `<nav aria-label>`, translated labels, `aria-current` | S |
| 0.4 | System theme: honour `systemmode` in `app.jsx` (`enableSystem`) | S |
| 0.5 | `<h1>` + `<Head>` title per page, skip link + `<main>` in `AdminLayout` | S |
| 0.6 | Accessible combobox for `searchable-select.jsx` (roles, arrow keys, Escape, i18n) | S |
| 0.7 | Submit spinners on form buttons (`isSubmitting` currently only disables) | S |

### Tranche 1 — Moroccan table stakes (S/M, additive schema)

| # | Item | Flag | Effort |
| --- | --- | --- | :-: |
| 1.1 | Vehicle document dates: `insurance_expiry`, `vignette_expiry`, `technical_inspection_expiry` (+ policy n°) → feed existing Reminders + dashboard Immediate Actions | — | S |
| 1.2 | Caution as a first-class object on bookings: type (empreinte CB / chèque / espèces / none), amount, held/released dates, deductions; printed on the contract | — | S–M |
| 1.3 | Franchise, km limit, km départ/retour, extra-km rate on booking + contract; `PricingServiceContract` computes extra-km | — | S |
| 1.4 | WhatsApp share (`wa.me` deep link with prefilled text) on Booking/Show, RentalAgreement/Show, Tva/Show; `+212` phone normalisation | `whatsapp` | S |
| 1.5 | Weekly / monthly rates on vehicles, chosen by `PricingServiceContract`; seasonal grid later | — | S–M |
| 1.6 | Dashboard "Départs du jour" / "Retours du jour" lists | — | S |
| 1.7 | Defaults hygiene: a per-client `default_timezone` in `config/clients/drivedesk.php` (`Africa/Casablanca`) read by `settingsKeys()` — the core default stays `Pacific/Tahiti` so other tenants are unchanged (CLAUDE.md §10.2 rules 1–2); RIB/IBAN instead of IFSC (add columns, keep old key); ICE 15-digit validator on drivers/companies/tvas | — | S |
| 1.8 | Reminder e-mails: fix the `emails.*` → `email.*` view namespace in `ReminderController`, add the missing `daily_reminder_summary` template, and stop swallowing the exception (surface via Sentry) | — | S |
| 1.9 | Late-return fee: grace period + hourly/daily rate on the booking, computed by `PricingServiceContract` and shown on the contract | — | S |

### Tranche 2 — differentiators (M/L)

| # | Item | Flag | Effort |
| --- | --- | --- | :-: |
| 2.1 | État des lieux: guided multi-photo capture on Inspections (departure/return pairs, timestamp, signature), mobile-first, PDF | `inspection_photos` | M |
| 2.2 | Bilingual FR/AR contract PDF with true RTL (dompdf + Cairo, per-client terms in both languages) | — | M |
| 2.3 | DGI e-invoicing: UBL 2.1 export per `tvas` row, ICE/IF validation, then a clearance adapter when the DGI API is published | `e_invoicing` | M–L |
| 2.4 | PV: resident / non-resident routing, advance + re-invoice line with processing fee | `traffic_violations` (existing) | S |
| 2.5 | Cahier des charges compliance file (fleet count vs 7, CNSS, licence, bonds, expiries) | `compliance_file` (new) | S–M |
| 2.6 | Online deposit prepay via YouCan Pay / CMI, replacing the dead Stripe/PayPal settings. Becomes the enforcement point of the **existing** `booking_payment` flag (no new key); `stripe` / `paypal` are retired in the same PR | `booking_payment` (existing) | M |
| 2.7 | Shared `DataTable` (server pagination, sort, per-page, bulk) extracted from `Booking/Index.jsx`, adopted on the 16 client-filtered lists | — | M |
| 2.8 | Mobile: table → card fallback, `md:grid-cols-2` forms, responsive Settings/Auth | — | M |
| 2.9 | Contract creation wizard with CIN/permis capture | — | M |
| 2.10 | Planning: drag-to-extend/move with conflict detection; decide FullCalendar Premium licence vs. a React Gantt | — | M |
| 2.11 | Reports: utilisation, revenue per vehicle, receivables, cautions outstanding; Excel/PDF export | — | M |

### Tranche 3 — growth

| # | Item | Flag | Effort |
| --- | --- | --- | :-: |
| 3.1 | Per-agency storefront / embeddable booking widget (port the Blade storefront) | `public_storefront` (existing) | M |
| 3.2 | Marketplace / OTA feeds (Karvyx, LocalRent, OneClickDrive) | `channels` (new) | L |
| 3.3 | REST API with Sanctum tokens; PWA field app for the état des lieux | `api` (new) | L |
| 3.4 | In-app notification centre; WhatsApp Business API | `whatsapp` (new, shared with 1.4) | M |
| 3.5 | Accounting CSV export (Sage / EBP formats) | — | S |
| 3.6 | Real multi-branch (fleet per branch, branch on bookings/users) — the enforcement point of the existing `multi_branch` flag | `multi_branch` (existing) | L |

### Cleanup

Ordered by the constraints in `CLAUDE.md` §4/§8 and `docs/migration-plan.md`
Phase 7 (item 0: "leave the DB tables in place; drop them in a post-migration
schema cleanup PR after Phase 8"). The "additive only" rule at the top of §6
applies here too.

*Safe now (no schema, no translation keys):* drop the dangling `HomeController`
imports; delete `ui-test/*` and `/hello`; finish or remove
`/newsletter/subscribe`; remove `composer.lock.backup`; rename `rentcar` →
DriveDesk in `package.json`, `.env.example`, `CLAUDE.md`; correct
`docs/phase6-execution-plan.md`; merge the two vitest trees; remove the
unservable `nl` entry from `supported_locales`; for each of the seven no-op
flags either add its enforcement point (2.6, 3.6) or delete the key with a
matching edit to every `config/clients/*.php`.

*Phase 6 exit gate:* finish the Blade tail (~30 files, list in §1) — which also
retires the Blade `feature('subscriptions')` branches, after which that flag
can go too.

*After Phase 8 only, each in its own ticket:* drop the `coupons`,
`coupon_histories`, `subscriptions`, `package_transactions` tables; remove the
orphan coupon / subscription translation keys and the ten locale bundles
`SetLocale` never serves (CLAUDE.md §4 requires a follow-up ticket for any key
removal).

---

## 7. Consequences for sales collateral

`docs/sales/README.md` "Known gaps" stays accurate. Each handbook §9 line maps
to one backlog item and may be rewritten only after that item is merged and
verified in the running app: *automatic deposit and late-fee calculation* →
1.2 **and** 1.9 (both; deposit alone does not clear the line); *reminder
e-mails / SMS* → 1.8 (e-mail half only — SMS stays a no); *daily rates only* →
1.5; *online payment* → 2.6; *photo damage capture* → 2.1; *multi-branch* →
3.6; *accounting integration* → 3.5; *mobile app* → 3.3.
