# Performance Audit — rentcar

**Audit date:** 2026-05-24
**Phase:** Phase 0 baseline — findings only, no fixes
**Auditor:** Ahmed (static analysis) + Claude Code (code review)
**Branch:** `feat/modernization`

---

## Phase 7 Delta — 2026-06-02

**Branch:** `dev` (all PRs merged)
**Tickets:** BAN-233 · BAN-234 · BAN-235 · BAN-236 · BAN-237

### Fixes shipped

| Ticket | Finding(s) | Fix | PR | Impact |
|--------|-----------|-----|----|--------|
| BAN-233 | F-01 + F-14 | `settings()` wrapped in `Cache::remember("settings_{userId}", 300)`. `flushSettingsCache()` called in all 7 `SettingController` write methods + `storeSignature`. | #69 | 2–15 fewer queries per request; captcha side-effect kept outside cache so it runs every request from cached data. |
| BAN-234 | F-04, F-06, F-07 | `->get()` → `->paginate(25)` in `BookingController::index()`, `VehicleController::index()`, `ExpenseController::index()`. `->map()` → `->through()` to preserve paginator metadata. New shared `Pagination.jsx` component with Inertia prev/next links. | #70 | Memory O(table) → O(25). List pages safe at any dataset size. |
| BAN-235 | F-02 | `organizationByMonth()` (12 × COUNT) and `incomeExpenseByMonth()` (24 × SUM) both replaced with single `GROUP BY MONTH(...)` queries. 12-slot arrays filled in PHP from sparse result. | #71 | 36 queries → 3 per dashboard load. |
| BAN-236 | F-13 | Pre-fetch before import loop: `$nextDriverId = max('driver_id') + 1`, `$nextVehicleId = max('vehicle_id') + 1`, `$existingEmails = User::pluck('email')` as in-memory set. Email while-loop and two `->latest()->first()` calls eliminated. | #72 | 200–300 fewer queries on 500-row import with new drivers/vehicles. Unbounded email-uniqueness loop eliminated. |
| BAN-237 | F-09 | `InspectionType::find($k)` inside checklist foreach replaced with `findMany(array_keys(...))->keyBy('id')` before the loop. Null-safe `?->type ?? ''` also fixes latent crash on deleted types. | #73 | N queries → 1 per inspection show page. |

### Updated finding status

| Finding | Phase 0 Priority | Phase 6 Status | Phase 7 Status |
|---------|-----------------|----------------|----------------|
| F-01 `settings()` uncached | P0 | 🔴 Open | ✅ **Fixed** (BAN-233 / PR #69) |
| F-02 Dashboard aggregate loops | P0 | 🟡 Partial (12 remain) | ✅ **Fixed** (BAN-235 / PR #71) |
| F-03 Settings redundancy via helper chain | P0 | 🔴 Open | ✅ **Fixed** (free fix from BAN-233) |
| F-04 Booking list unbounded `->get()` | P1 | 🔴 Open | ✅ **Fixed** (BAN-234 / PR #70) |
| F-05 Booking planning `->get()` + N+1 | P1 | 🔴 Open | 🔴 **Open** (not in Phase 7 scope) |
| F-06 Vehicle list unbounded `->get()` | P1 | 🔴 Open | ✅ **Fixed** (BAN-234 / PR #70) |
| F-07 Expense list unbounded `->get()` | P1 | 🔴 Open | ✅ **Fixed** (BAN-234 / PR #70) |
| F-08 Booking create 4 unbounded `->get()` | P2 | 🔴 Open | 🔴 **Open** (not in Phase 7 scope) |
| F-09 Inspection show N+1 | P2 | 🔴 Open | ✅ **Fixed** (BAN-237 / PR #73) |
| F-10 Subscription save loop | P2 | ✅ Fixed | ✅ Fixed |
| F-11 RentalAgreement sequential lookups | P3 | 🔴 Open | 🔴 **Open** (not in Phase 7 scope) |
| F-12 Subscription landing `->get()` | P3 | ✅ Fixed | ✅ Fixed |
| F-13 Excel import per-row queries | P1 | 🔴 Open | ✅ **Fixed** (BAN-236 / PR #72) |
| F-14 `settings()` in Inertia middleware | P0 (new) | 🔴 Open | ✅ **Fixed** (BAN-233 / PR #69) |

### Remaining open findings

- **F-05** — `BookingController::planning()` N+1 on `$booking->drivers` (one `with('drivers')` line — schedule as BAN-238)
- **F-08** — `BookingController::create()` 4 unbounded `->get()` on form load (schedule as BAN-239)
- **F-11** — `RentalAgreementController::show()` sequential User/Driver lookups (schedule as BAN-240)

---

## Phase 6 Delta — 2026-06-02

**Branch:** `dev` (post Phase 6 merge)
**Method:** Static code analysis comparing Phase 0 findings against current source.

### Free wins from Phase 6

| Finding | Status | Detail |
|---------|--------|--------|
| F-10 | ✅ **Fixed** | `assignSubscription()` / `assignManuallySubscription()` deleted in PR #67. Row-by-row save loop is gone. |
| F-12 | ✅ **Fixed** | `Subscription::get()` on landing page deleted with the billing layer in PR #67. |
| F-02 | 🟡 **Partially improved** | `paymentByMonth()` (12 SUM queries on `package_transactions`) removed in PR #67. Super-admin dashboard drops from 48 → 12 queries per load. `organizationByMonth()` (12 COUNT) and `incomeExpenseByMonth()` (24 SUM) still fire in loops. |
| — | ✅ **New win** | jQuery (~87 KB) and Alpine.js (~14 KB) removed from JS bundle. React SPA means subsequent page navigations are client-side — no server round-trip for HTML. |
| — | ✅ **New win** | Vite replaces webpack: tree-shaken, split-chunk bundles. `srmklive/paypal`, `stripe/stripe-php`, `mashape/unirest-php` removed from Composer autoloader. |

### New finding since Phase 0

**F-14 (new): `settings()` called in `HandleInertiaRequests` middleware on every request**

- **File:** `app/Http/Middleware/HandleInertiaRequests.php:131`
- `$this->cachedSettings = settings()` fires once per Inertia request via `share()`, in addition to any controller-level calls. This means the settings SELECT now runs on every page load (not just pages that explicitly call `settings()`). F-01 is therefore more critical than Phase 0 assessed — the middleware call alone adds 1 uncached query to the baseline of every page.
- **Priority:** P0 (same fix as F-01 — cache `settings()` and both problems are resolved together)

### Status of all Phase 0 findings

| Finding | Phase 0 Priority | Phase 6 Status |
|---------|-----------------|----------------|
| F-01 `settings()` uncached | P0 | 🔴 **Open** — compounded by F-14 |
| F-02 Dashboard aggregate loops | P0 | 🟡 **Partial** — 12 queries remain (was 48) |
| F-03 Settings redundancy via helper chain | P0 | 🔴 **Open** |
| F-04 Booking list unbounded `->get()` | P1 | 🔴 **Open** |
| F-05 Booking planning `->get()` + N+1 | P1 | 🔴 **Open** |
| F-06 Vehicle list unbounded `->get()` | P1 | 🔴 **Open** |
| F-07 Expense list unbounded `->get()` | P1 | 🔴 **Open** |
| F-08 Booking create 4 unbounded `->get()` | P2 | 🔴 **Open** |
| F-09 Inspection show N+1 | P2 | 🔴 **Open** |
| F-10 Subscription save loop | P2 | ✅ **Fixed** |
| F-11 RentalAgreement sequential lookups | P3 | 🔴 **Open** |
| F-12 Subscription landing `->get()` | P3 | ✅ **Fixed** |
| F-13 Excel import per-row queries | P1 | 🔴 **Open** |
| F-14 `settings()` in Inertia middleware | — | 🔴 **New (P0)** |

### Top 5 for Phase 7 tickets

1. **F-01 + F-14** — Cache `settings()` helper — eliminates 2–15 queries per request; biggest single fix.
2. **F-04 / F-06 / F-07** — Paginate list pages (bookings, vehicles, expenses) — prevents memory exhaustion at scale.
3. **F-02** — Batch remaining dashboard aggregates (`organizationByMonth`, `incomeExpenseByMonth`) into GROUP BY queries.
4. **F-13** — Fix Excel import per-row query patterns.
5. **F-09** — Fix InspectionController N+1 on show page.

---

> **Methodology note:** This audit combines static code analysis (verified
> against the source at the commit on `feat/modernization`) with a template
> for live profiling numbers. Columns marked **TBD** must be filled in by
> running the app with Telescope enabled (`TELESCOPE_ENABLED=true`) and
> real/production-shaped data. See `docs/perf-audit-plan.md` for the
> profiling procedure.

---

## Environment

| Field                     | Value                                        |
| ------------------------- | -------------------------------------------- |
| PHP version               | _fill in: `php -v`_                          |
| Laravel version           | 10.48                                        |
| MySQL version             | _fill in: `SELECT VERSION();`_               |
| Telescope version         | 5.20                                         |
| Debugbar version          | 3.16                                         |
| Dataset size (bookings)   | _fill in: `SELECT COUNT(*) FROM bookings;`_  |
| Dataset size (vehicles)   | _fill in: `SELECT COUNT(*) FROM vehicles;`_  |
| Dataset size (users)      | _fill in: `SELECT COUNT(*) FROM users;`_     |
| Dataset size (settings)   | _fill in: `SELECT COUNT(*) FROM settings;`_  |
| Slow-query threshold      | 200ms (`long_query_time = 0.2`)              |

---

## 1. Executive summary

Static analysis of the ten highest-traffic controllers reveals three
systemic performance problems:

1. **The `settings()` helper hits the database on every call with no
   caching.** It is called 21 times across controllers (plus indirectly
   via `getSettingsValByName`, `settingPriceFormat`, `formattedDate`,
   etc.), meaning a single page load can issue 5–15 redundant settings
   queries. At any meaningful request rate this will dominate DB time.

2. **The dashboard runs 36–48 sequential single-row aggregate queries
   in PHP loops** instead of one GROUP BY query per table. A super-admin
   dashboard load fires 12 COUNT queries (`organizationByMonth`) + 12
   SUM queries (`paymentByMonth`); an owner dashboard fires 24 SUM
   queries (`incomeExpenseByMonth`). These grow O(1) with dataset size
   but are structurally wasteful.

3. **All major list pages (`/bookings`, `/vehicles`, `/expenses`) use
   unbounded `->get()` with no pagination.** Every row in the table is
   fetched on every page load. With a production dataset this will cause
   memory exhaustion and multi-second response times.

Expected fastest wins after Phase 7:
- **F-01** — cache `settings()`: eliminates 3–14 redundant queries per page; F-03 is an automatic free fix.
- **F-02** — batch dashboard aggregates: 36–48 queries → 3–4 per load.
- **F-04/F-06/F-07** — paginate list pages: memory becomes O(page size) instead of O(table size).
- **F-05/F-09** — add eager loads / batch lookups: eliminates N+1 query explosions on planning and inspection views.

Together these should cut dashboard and list-page query counts by 80–95%.

---

## 2. Baseline metrics table

Fill in after running a live profiling session (see
`docs/perf-audit-plan.md` §"How to run an audit pass on one page").

| Page / endpoint                 | p50 (ms) | p95 (ms) | DB queries | Peak memory |
| ------------------------------- | -------- | -------- | ---------- | ----------- |
| Dashboard — owner               | TBD      | TBD      | TBD        | TBD         |
| Dashboard — super admin         | TBD      | TBD      | TBD        | TBD         |
| GET /booking (list)             | TBD      | TBD      | TBD        | TBD         |
| GET /booking/create             | TBD      | TBD      | TBD        | TBD         |
| GET /booking/show               | TBD      | TBD      | TBD        | TBD         |
| GET /booking/planning           | TBD      | TBD      | TBD        | TBD         |
| GET /vehicle (list)             | TBD      | TBD      | TBD        | TBD         |
| GET /expense (list)             | TBD      | TBD      | TBD        | TBD         |
| GET /inspection (list)          | TBD      | TBD      | TBD        | TBD         |
| GET /reminder (list)            | TBD      | TBD      | TBD        | TBD         |
| POST /booking (Excel import)    | TBD      | TBD      | TBD        | TBD         |
| GET /rental-agreement/show (PDF)| TBD      | TBD      | TBD        | TBD         |
| GET /setting                    | TBD      | TBD      | TBD        | TBD         |

---

## 3. Prioritized findings

### F-01: `settings()` helper — uncached DB query on every call

- **File:** `app/Helper/helper.php:93–120`
- **Symptom:** TBD — measure with Telescope "Queries" tab on any page
  that calls `settings()`.
- **Likely cause:** `settings()` runs a full `SELECT * FROM settings WHERE parent_id = ?`
  on every invocation. There is no per-request or cross-request cache.
  The function is called **21 times across controllers** (confirmed via
  `grep -rn "settings()" app/Http/Controllers/`) plus indirectly through
  `getSettingsValByName()`, `settingPriceFormat()`, `formattedDate()`,
  and `formattedTime()`. A booking create form alone calls it 4+ times.
- **Evidence (static):**
  ```php
  // helper.php:93–102 — full query every call, no Cache::remember
  function settings() {
      $settingData = DB::table('settings');
      if (\Auth::check()) {
          $userId = parentId();
          $settingData = $settingData->where('parent_id', $userId);
      }
      $settingData = $settingData->get();
  ```
- **Fix sketch:** Wrap in `Cache::remember("settings_{$userId}", 300, fn() => ...)`.
  Invalidate the key when `SettingController` saves. ~10 lines.
- **Estimated effort:** S (≤30 min)
- **Estimated impact:** Eliminates 3–14 redundant queries per page. On a
  busy owner dashboard this is likely the single biggest win.
- **Risk:** Low — additive cache. Must flush on settings save (one line
  in `SettingController::generalData()`).
- **Priority:** P0

---

### F-02: Dashboard — 36–48 aggregate queries fired in PHP loops

- **File:** `app/Http/Controllers/HomeController.php`
- **Symptom:** TBD — expect 40+ queries on super-admin dashboard, 28+
  on owner dashboard.
- **Likely cause:** Three methods loop over 12 months and fire one DB
  aggregate per iteration instead of using a single `GROUP BY` query:

  | Method                 | Lines     | Queries/load | Table              |
  | ---------------------- | --------- | ------------ | ------------------ |
  | `organizationByMonth`  | 83–90     | 12 × COUNT   | `users`            |
  | `paymentByMonth`       | 104–110   | 12 × SUM     | `package_transactions` |
  | `incomeExpenseByMonth` | 125–133   | 12 × SUM × 2 | `bookings`, `expenses` |

- **Evidence (static):**
  ```php
  // HomeController.php:88 — one COUNT per loop iteration
  while ($currentdate <= $end) {
      $organization['data'][] = User::where('type', 'owner')
          ->whereMonth('created_at', $month)
          ->whereYear('created_at', $year)
          ->count();   // ← 12 separate queries
  }
  ```
- **Fix sketch:** Replace each loop with a single query:
  ```php
  User::where('type', 'owner')
      ->whereYear('created_at', date('Y'))
      ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
      ->groupByRaw('MONTH(created_at)')
      ->pluck('total', 'month');
  ```
  Then fill the 12-slot array from the result collection. ~15 lines each.
- **Estimated effort:** M (1–2 hours for all three methods)
- **Estimated impact:** 36–48 queries → 3–4 queries per dashboard load.
- **Risk:** Low — pure read path, no writes.
- **Priority:** P0

---

### F-03: `getSettingsValByName('landing_page')` on unauthenticated landing

- **File:** `app/Http/Controllers/HomeController.php:63`
- **Symptom:** Every unauthenticated visitor to `/` fires a full settings
  query even before the landing page is rendered.
- **Likely cause:** `getSettingsValByName()` calls `settings()` internally,
  which is uncached (see F-01).
- **Evidence (static):** Line 63: `$landingPage = getSettingsValByName('landing_page');`
- **Fix sketch:** Resolved automatically when F-01 is fixed (cache covers
  unauthenticated calls via `parent_id = 1` path).
- **Estimated effort:** XS (fixed by F-01)
- **Estimated impact:** Low in isolation; covered by F-01.
- **Risk:** None.
- **Priority:** P1 (fixed as part of F-01)

---

### F-04: `BookingController::index()` — unbounded `->get()` loads all bookings

- **File:** `app/Http/Controllers/BookingController.php:31`
- **Symptom:** TBD — measure memory and query time with 5,000+ bookings.
  Likely >500ms and >50MB at production scale.
- **Likely cause:** No pagination. All bookings for the tenant are fetched
  into memory on every list page load.
- **Evidence (static):**
  ```php
  // BookingController.php:31
  $bookings = Booking::where('parent_id', '=', parentId())
      ->orderBy('created_at', 'desc')
      ->get();   // ← no limit, no paginate
  ```
- **Fix sketch:** Replace `->get()` with `->paginate(20)` and add
  `{{ $bookings->links() }}` to the Blade view.
- **Estimated effort:** S (≤1 hour including view change)
- **Estimated impact:** Memory drops from O(n bookings) to O(20). Response
  time becomes constant regardless of dataset size.
- **Risk:** Low — pagination is additive. Existing filters still work;
  just need to thread the page parameter through.
- **Priority:** P1

---

### F-05: `BookingController::planning()` — N+1 on `$booking->drivers`

- **File:** `app/Http/Controllers/BookingController.php:1116–1130`
- **Symptom:** TBD — expect 1 query per booking for driver lookup.
  With 500 bookings: 500+ extra queries.
- **Likely cause:** All bookings are fetched without eager loading the
  driver relationship, then `$booking->drivers->name` is accessed inside
  the `foreach` loop triggering one lazy-load per booking.
- **Evidence (static):**
  ```php
  // BookingController.php:1116, 1130
  $bookings = Booking::where('parent_id', $parentId)->get();  // no with()
  foreach ($bookings as $booking) {
      $driver = !empty($booking->drivers) ? $booking->drivers->name : '';  // N+1
  ```
- **Fix sketch:** `Booking::where('parent_id', $parentId)->with('drivers')->get();`
  — one extra word eliminates all driver queries.
- **Estimated effort:** XS (1 line)
- **Estimated impact:** Query count drops from N+2 to 2 regardless of
  booking count.
- **Risk:** None.
- **Priority:** P1

---

### F-06: `VehicleController::index()` — unbounded `->get()` on all vehicles

- **File:** `app/Http/Controllers/VehicleController.php:20`
- **Symptom:** TBD — measure with 1,000+ vehicles.
- **Likely cause:** No pagination on vehicle list.
- **Evidence (static):**
  ```php
  // VehicleController.php:20
  $vehicles = Vehicle::where('parent_id', '=', parentId())->get();
  ```
- **Fix sketch:** `->paginate(20)`. Also applies to `create()` (line 42)
  where vehicles are loaded for the booking form dropdown — consider
  switching to an AJAX autocomplete for large fleets.
- **Estimated effort:** S
- **Estimated impact:** Constant memory regardless of fleet size.
- **Risk:** Low.
- **Priority:** P1

---

### F-07: `ExpenseController::index()` — unbounded `->get()` on all expenses

- **File:** `app/Http/Controllers/ExpenseController.php:16` (confirmed
  by grep — line numbers subject to verification).
- **Symptom:** TBD — grows linearly with years of expense records.
- **Fix sketch:** `->paginate(20)`.
- **Estimated effort:** XS
- **Estimated impact:** Constant memory.
- **Risk:** None.
- **Priority:** P1

---

### F-08: `BookingController::create()` — 4 unbounded `->get()` calls per form load

- **File:** `app/Http/Controllers/BookingController.php:42–55`
- **Symptom:** TBD — slow booking form at scale, especially vehicle
  and driver dropdowns.
- **Likely cause:** Four unscoped `->get()` calls load every vehicle,
  driver, place, and addon for the tenant on every create-form render.
- **Evidence (static):**
  ```php
  // BookingController.php:42–55
  $vehicles = Vehicle::where('parent_id', parentId())->get();         // all
  $drivers  = User::where('parent_id', parentId())
      ->where('type', 'driver')->orderBy('created_at', 'desc')->get(); // all
  $places   = Place::where('parent_id', parentId())->get();            // all
  $addon    = Addon::where('parent_id', parentId())->get()->pluck(...); // all
  ```
- **Fix sketch:** Short-term: add `->limit(500)` on each. Long-term
  (Phase 5+): replace dropdowns with server-side AJAX search.
- **Estimated effort:** S short-term, M long-term
- **Estimated impact:** Prevents OOM on large tenants; improves TTI on
  the create form.
- **Risk:** Low — limits are conservative and well above typical fleet sizes.
- **Priority:** P2

---

### F-09: `InspectionController::show()` — N+1 on `InspectionType::find()` in loop

- **File:** `app/Http/Controllers/InspectionController.php` (verify exact
  lines — agent found this at ~106–111).
- **Symptom:** TBD — one `SELECT` per checklist item on each inspection
  view.
- **Likely cause:** `InspectionType::find($k)` called inside a foreach
  loop over the checklist JSON keys.
- **Fix sketch:** Collect all checklist IDs first, then
  `$types = InspectionType::findMany($checklistIds)->keyBy('id');`
  and look up from the collection inside the loop.
- **Estimated effort:** S
- **Estimated impact:** Drops from N queries to 1 per inspection view.
- **Risk:** None.
- **Priority:** P2

---

### F-10: `helper.php` — row-by-row `->save()` loop in subscription assignment

- **File:** `app/Helper/helper.php` — `assignSubscription()` and
  `assignManuallySubscription()` (lines ~286–306 and ~334–354).
- **Symptom:** TBD — slow subscription toggle when tenant has many users.
- **Likely cause:** Each User model is fetched then saved individually
  inside a `foreach` loop instead of using a batch SQL UPDATE.
- **Fix sketch:** Replace the save loop with:
  `User::where('parent_id', parentId())->whereNotIn('type', ['driver'])->update(['is_active' => 1]);`
- **Estimated effort:** S
- **Estimated impact:** O(n) queries → 1 query per subscription event.
- **Risk:** Low — same WHERE clause, different execution strategy.
- **Priority:** P2

---

### F-11: `RentalAgreementController::show()` — multiple sequential User/Driver lookups

- **File:** `app/Http/Controllers/RentalAgreementController.php` (~lines 186–188).
- **Symptom:** 2–3 extra queries per rental agreement view.
- **Likely cause:** Driver 1 and Driver 2 are fetched with separate
  `User::find()` and `Driver::where()->first()` calls after the main
  record is loaded, instead of being eager loaded.
- **Fix sketch:** Add `->with(['primaryDriver', 'secondaryDriver'])` to
  the initial `RentalAgreement` query (requires the relationships to be
  defined on the model).
- **Estimated effort:** S
- **Estimated impact:** Minor — 2–3 queries saved per page view.
- **Risk:** Low.
- **Priority:** P3

---

### F-12: `HomeController::index()` — `Subscription::get()` on landing page (no limit)

- **File:** `app/Http/Controllers/HomeController.php:66`
- **Symptom:** Every unauthenticated visitor loads all subscription plans.
- **Evidence (static):** `$subscriptions = Subscription::get();`
- **Fix sketch:** Plans table is small and rarely changes — wrap in
  `Cache::remember('subscriptions', 3600, ...)`. Or at minimum add
  `->where('active', true)` if such a column exists.
- **Estimated effort:** XS
- **Estimated impact:** Minimal — table is small. Cache removes the query
  entirely on a busy landing page.
- **Risk:** None.
- **Priority:** P3

---

### F-13: `BookingController::importExcel()` — per-row DB queries inside import loop

- **File:** `app/Http/Controllers/BookingController.php:622–809`
- **Symptom:** TBD — import time grows linearly with row count; likely
  multi-second for files with hundreds of rows.
- **Likely cause:** Three query patterns fire inside (or nested inside)
  the main `foreach` row loop:

  1. **Email-uniqueness while-loop (line 723):** For each new driver, a
     `User::where('email', $email)->exists()` query runs in a `while`
     loop until a unique email is found — potentially unbounded queries
     per driver.
  2. **`Driver::latest()->first()` per new driver (line 743):** Fetches
     the highest `driver_id` individually for every new driver row
     instead of tracking the max in memory.
  3. **`Vehicle::latest()->first()` per new vehicle (line 757):** Same
     pattern — re-queries the max `vehicle_id` on every new vehicle.

  Drivers and vehicles already have an in-memory cache (`$driversCache`,
  `$vehiclesCache` at lines 649–650) for lookups; the `->latest()->first()`
  calls are the gap in that optimization.

- **Evidence (static):**
  ```php
  // BookingController.php:723 — while-in-foreach
  while (User::where('email', $email)->exists()) { ... }

  // BookingController.php:743 — re-queries max driver_id per new driver
  $latestDriver = Driver::where('parent_id', $pid)->latest()->first();

  // BookingController.php:757 — re-queries max vehicle_id per new vehicle
  $latestVehicle = Vehicle::where('parent_id', $pid)->latest()->first();
  ```
- **Fix sketch:**
  - Before the loop, resolve the next available `driver_id` and
    `vehicle_id` once and increment in memory:
    ```php
    $nextDriverId  = (Driver::where('parent_id', $pid)->max('driver_id') ?? 0) + 1;
    $nextVehicleId = (Vehicle::where('parent_id', $pid)->max('vehicle_id') ?? 0) + 1;
    ```
  - For email uniqueness, pre-load existing emails into a `Set` before
    the loop rather than querying on each iteration.
- **Estimated effort:** S (≤1 hour)
- **Estimated impact:** Eliminates 2–3 queries per new driver/vehicle row.
  For a 500-row import with 100 new drivers/vehicles: saves 200–300
  queries. Email-uniqueness loop elimination removes unbounded query risk.
- **Risk:** Low — in-memory counter produces the same IDs as the DB
  re-query, assuming no concurrent imports (single-user admin tool).
- **Priority:** P1

---

## 4. Sanity-check checklist

Run these checks and tick them off. Items already confirmed from code
review are pre-ticked.

- [ ] `OPCACHE` enabled in production PHP-FPM
- [ ] `config:cache`, `route:cache`, `view:cache` run on deploy
- [ ] `composer install --optimize-autoloader --no-dev` in production
- [ ] Asset caching headers (Cache-Control, ETag) set by the web server
- [ ] Gzip/Brotli compression enabled at the web server
- [ ] Database indexes on foreign keys (check `SHOW INDEX FROM bookings;` etc.)
- [ ] Indexes on `bookings.start_date`, `bookings.parent_id`, `bookings.status`
- [ ] Indexes on `vehicles.parent_id`, `expenses.parent_id`
- [ ] Indexes on `reminders.parent_id`, `reminders.reminder_date`
- [ ] Indexes on `inspections.parent_id`
- [ ] `SESSION_DRIVER` not `file` in production under load
- [ ] `QUEUE_CONNECTION` not `sync` for PDF generation and mail
- [x] `TELESCOPE_ENABLED=false` in production (enforced via `.env.example` default)
- [x] `DEBUGBAR_ENABLED=false` in production (enforced via `.env.example` default)

---

## 5. Re-measurement section

Phase 7 query counts are from static analysis / test assertions. p50 requires live profiling with production-shaped data (see `docs/perf-audit-plan.md`).

| Finding | Pre-fix queries | Post-fix queries | Notes | Pre-fix p50 | Post-fix p50 |
| ------- | --------------- | ---------------- | ----- | ----------- | ------------ |
| F-01 + F-14 | 2–15 per request | 1 (cached, 5-min TTL) | Flushed on settings save | TBD | TBD |
| F-02    | 36 (12 COUNT + 24 SUM) | 3 (3 GROUP BY) | Owner: 2, super-admin: 3 | TBD | TBD |
| F-04    | O(n bookings)   | O(25)            | paginate(25) | TBD | TBD |
| F-05    | N+2 (N = booking count) | Open | Not fixed in Phase 7 | TBD | TBD |
| F-06    | O(n vehicles)   | O(25)            | paginate(25) | TBD | TBD |
| F-07    | O(n expenses)   | O(25)            | paginate(25) | TBD | TBD |
| F-08    | 4 unbounded get() | Open           | Not fixed in Phase 7 | TBD | TBD |
| F-09    | N (N = checklist items) | 1         | findMany() batch load | TBD | TBD |
| F-10    | Fixed Phase 6   | Fixed Phase 6    | — | — | — |
| F-13    | 2–3 per new driver/vehicle row | 0 per row | Pre-fetched counters + email set | TBD | TBD |

---

_Phase 7 complete as of 2026-06-02. Open findings (F-05, F-08, F-11) to be scheduled as BAN-238/239/240 in the next planning cycle. Live p50/p95 measurements require a Telescope-enabled run against production-shaped data — see `docs/perf-audit-plan.md`._

---

## Database Index Audit — 2026-06-09 (production-data analysis)

**Branch:** `dev`
**Method:** the `directonderweg` **production** database was imported into a local
MySQL copy (38 tables; ~10.6k TVAs, 1.5k bookings, 1.4k rental agreements, 1k
drivers/users). `EXPLAIN` was run on representative tenant-scoped queries and
`information_schema` was used to enumerate existing indexes.
**Scope:** schema/index findings only — distinct from the code-level findings
F-01…F-14. **Report-only** per §7; the proposed index migration ships as a
separate, approved `perf:` PR per §4.

### Headline

The app is **multi-tenant** — every list/report query is scoped by `parent_id`
(the owning agency) — yet **`parent_id` is unindexed on every major table**.
Existing indexes are essentially just primary keys plus a few auto-generated FK
indexes (`bookings.tva_id`, `reminders.id_vehicle`, `reminders.reminder_type_id`,
`signatures.user_id`, `users.email`). Result: every list and report does a
**full table scan** that grows with *total* rows across all tenants, not just the
current tenant's.

### Evidence (`EXPLAIN`, real data)

| Representative query | type | key | rows scanned | Extra |
|---|---|---|---|---|
| `tvas WHERE parent_id=? AND year=? AND month=? AND deleted_at IS NULL` | `ALL` | `NULL` | **10,592** | Using where |
| `bookings WHERE parent_id=? ORDER BY created_at DESC LIMIT 10` | `ALL` | `NULL` | **1,364** | Using where; **Using filesort** |

One tenant already owns all 10,711 TVA rows, so the report scan is effectively
unbounded per that tenant.

### Findings

### F-15: `parent_id` unindexed on all tenant-scoped tables
- **Page / endpoint:** every list/index (bookings, drivers, vehicles, credits, expenses, inspections, reminders, rental agreements, users) and the dashboard.
- **Symptom:** full table scan on each load; cost grows with total rows across all tenants, not just the current tenant's.
- **Likely cause:** no index on `parent_id` on bookings, booking_payments, credits, drivers, expenses, inspections, logged_histories, reminders, rental_agreements, tvas, users, vehicles.
- **Evidence:** `information_schema.statistics` shows only PK + a few FK indexes; EXPLAIN `type: ALL`, `key: NULL` (table above).
- **Fix sketch:** composite indexes leading with `parent_id` (see proposed migration). Additive — no schema/data change.
- **Estimated effort:** S (one migration).
- **Estimated impact:** list/report queries go from O(all rows) full scan to an index range scan over the tenant's rows; compounds as data grows.
- **Risk:** low — additive indexes; brief lock on `ADD INDEX` (online DDL on MySQL 5.7+/8 for these sizes; use a window if a future table is very large).
- **Priority:** P0

### F-16: TVA report — full scan of the largest table
- **Page / endpoint:** TVA index + report (`TvaController`), filtered by `parent_id` + `year` + `month`.
- **Symptom:** scans all 10,592 TVA rows per report load; `tvas` also uses `SoftDeletes`, so `deleted_at IS NULL` is appended to every query.
- **Likely cause:** no composite `(parent_id, year, month)`; no FK index on `tvas.booking_id`.
- **Evidence:** EXPLAIN `type: ALL`, rows 10,592, key `NULL`.
- **Fix sketch:** `tvas (parent_id, year, month)` + `tvas (booking_id)`. Add `deleted_at` to the composite only if profiling shows residual cost.
- **Estimated effort:** S.
- **Estimated impact:** report query becomes an index range scan (a few rows) — the single biggest query win in the app.
- **Risk:** low.
- **Priority:** P0

### F-17: foreign-key join columns unindexed
- **Endpoints:** anything joining bookings↔tvas↔payments, drivers↔users, and the audit log.
- **Likely cause:** `tvas.booking_id`, `booking_payments.booking_id`, `drivers.user_id`, `logged_histories.parent_id`/`user_id` have no index.
- **Fix sketch:** FK indexes (see migration); `logged_histories (parent_id, created_at)`.
- **Estimated effort:** S. **Estimated impact:** removes per-join scans. **Risk:** low. **Priority:** P1

### F-18: `rental_agreements` fat-row `SELECT *`
- **Page / endpoint:** rental agreements list.
- **Symptom:** 6.5 MB for 1,359 rows (~4.8 KB/row) because of `terms_condition` + `description` (`TEXT`); the list pulls these big columns it doesn't render.
- **Fix sketch:** select only the columns the list needs (or move the large text to a side table). **Code-level**, not an index.
- **Estimated effort:** S–M. **Estimated impact:** less I/O + memory per list load. **Risk:** low. **Priority:** P2

### F-19: `logged_histories` unbounded growth
- **Symptom:** audit-log table grows without bound (1.8k rows and climbing); no index for its queries.
- **Fix sketch:** `(parent_id, created_at)` index **and** a retention/prune policy (delete > N months, or a scheduled prune job).
- **Estimated effort:** S (index) + S (prune job). **Estimated impact:** prevents slow degradation. **Risk:** low. **Priority:** P2

### F-20: search uses leading-wildcard `LIKE '%term%'`
- **Page / endpoint:** driver/booking/vehicle search boxes.
- **Symptom:** leading-wildcard `LIKE` can't use a B-tree index → full scan on search.
- **Fix sketch:** fine at current sizes; if drivers/bookings grow large, add `FULLTEXT` indexes or a dedicated search path.
- **Estimated effort:** M. **Estimated impact:** only matters at scale. **Risk:** low. **Priority:** P3

### Proposed fix

Index migration drafted in
`database/migrations/2026_06_09_000000_add_tenant_and_fk_indexes.php`
(PR `perf: tenant-scoped + FK indexes`, **merged** after benchmark approval per
§4/§7). Covers **F-15 / F-16 / F-17**. F-18 / F-19 / F-20 are code/ops
follow-ups — create dedicated tickets when scheduled (no number assigned yet).

**Status:** F-18 **fixed** — `perf: trim rental-agreement list query` (the list
now selects only displayed columns + eager-loads driver/vehicle, dropping the
`terms_condition`/`description` TEXT blobs and the per-row N+1). F-19 / F-20 open.

> Before/after `EXPLAIN` numbers from the local production copy are recorded in
> that PR's description once the migration is benchmarked.

### Page-level benchmark (2026-06-09) — what the indexes actually move

The index migration was applied to the local production copy and benchmarked
end-to-end. To remove the local dev-server boot floor (`php artisan serve` has
**no opcache**, so every request reparses the app — TTFB ~2.0 s, identical for a
no-DB route like `/login`), the built-in server was run with opcache +
`config:cache` (the same boot path production already uses). Numbers are warm,
5-sample medians.

| Page | Index OFF | Index ON | Δ |
|------|-----------|----------|---|
| `/booking` (1.4k-row table) | 352 ms | **311 ms** | ~40 ms (~12% faster) |
| `/tva` (10.6k-row table; 1,231 for the tenant) | 2,392 ms | **2,338 ms** | ~54 ms (~2% faster) |

Query layer (measured directly, `IGNORE INDEX` vs index): TVA report
**60 → 6.6 ms (~9×)**, bookings list **22.9 → 4.4 ms (~5×)**.

**Takeaways:** the indexes give a clean query-layer win and prevent full-table
scans that degrade under data growth + concurrency, but on a *single* request
they are a small slice of total time. The benchmark surfaced two findings that
dominate more than the indexes do:

### F-21: TVA index page loads the full result set + N+1
- **Page / endpoint:** `GET /tva` (`TvaController@index`).
- **Symptom:** ~2.3 s TTFB **even with the index and opcache** — the index is not the bottleneck. The page builds the whole tenant result (1,231 rows for this tenant; 10.6k total) and renders all of it, with per-row relation access (booking/driver) → N+1.
- **Evidence:** dropping/adding the indexes moved this page only ~2% (2,392 → 2,338 ms); the floor is the row count + N+1, not the filter scan.
- **Fix sketch:** `paginate(25)` the TVA list (as F-04/F-06/F-07 did for bookings/vehicles/expenses) and eager-load the displayed relations (`with([...])`). Mirrors the existing pagination pattern.
- **Estimated effort:** S–M. **Estimated impact:** the single biggest win on the TVA screen — multi-second → sub-second. **Risk:** low. **Priority:** P1

### F-22: duplicate route names block `route:cache` in production
- **Symptom:** `php artisan route:cache` throws `LogicException: Unable to prepare route [settings/account] for serialization. Another route has already been assigned name [setting.account].` Production therefore cannot use route caching (a real boot-time speedup), and `php artisan optimize` fails midway.
- **Likely cause:** GET and POST routes share a name (e.g. `settings/account` GET + POST both named `setting.account`; same pattern on `setting.general`, etc. in `routes/web.php`).
- **Fix sketch:** give the write (POST) routes distinct names (e.g. `setting.account.update`) and update the corresponding `route()` references. **NOTE:** route-name changes are a §4 frozen-surface change — must be a separate, explicit ticket, not bundled into the migration.
- **Estimated effort:** S (mechanical) but touches the frozen route surface. **Estimated impact:** enables `route:cache` (faster prod boot). **Risk:** medium (route-name change — verify every `route('setting.*')` call site). **Priority:** P2

F-21 and F-22 are open follow-ups — create dedicated tickets when scheduled
(F-22 is a route-surface change, handled with care per §4). No ticket numbers
assigned here (earlier drafts referenced BAN-241…245, which are unrelated
existing issues — corrected).
