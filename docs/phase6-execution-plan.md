# Phase 6 Execution Plan — Condensed Agentic Port (Opus 4.8 workflow)

Created: 2026-05-29
Owner: Ahmed
Status: proposal — pending sign-off

This plan condenses the remaining migration work (the Blade→React port and
the cleanup/deploy tail) into a small number of **autonomous agent missions**
that run in parallel git worktrees, each gated by the test suite the project
already built in Phase 1. It assumes the rules in `CLAUDE.md` are binding —
nothing here overrides "same functionality, end to end."

---

## 1. Where we are (2026-05-29)

| Phase | Scope | Status |
| --- | --- | --- |
| 0 — Safety net | perf audit, test catalogue, multi-client skeleton, CI, Sentry | ✅ Done |
| 1 — Feature test coverage | 462 tests green; money/signature flows covered | ✅ Done* |
| 2 — Laravel 10→11 | framework + deps + PHPUnit 11 | ✅ Done |
| 3 — Laravel 11→12 | + feature-flag gating of subscriptions/payments | ✅ Done |
| 4 — Mix→Vite | vite.config, `@vite()`, 10-page smoke test | ✅ Done |
| 5 — Inertia/React shell | Inertia+Ziggy, shadcn, theming, useZodForm, dashboard POC, Vitest | ✅ Done |
| **6 — Port Blade→React** | ~30 page groups | 🔴 **~13%** |
| 7 — Cleanup + perf fixes | delete dead SaaS code, top-5 perf, drop flag, README | ⬜ 0% |
| 8 — Merge + deploy | rebase, PR, tag v2.0.0, GitHub Environments, monitor | ⬜ 0% |

Overall: **~58% by story points (123 / 213)**. Backend modernization is
complete; the remaining ~90 points are the frontend port plus the
cleanup/deploy tail.

\* Phase 1 has a loose end — see Risk R3 (coverage gate still at 50%).

---

## 2. The condensing idea

Phase 6 is ideally shaped for autonomous execution: the work is repetitive,
each page group is independently scoped, and **the acceptance criteria already
exist** as the Phase 1 PHP feature tests. An agent doesn't need a human to tell
it whether a port is correct — it runs `php artisan test` + Vitest and reads
the red/green signal.

So instead of 15 loosely-ordered tickets, the remaining Phase 6 work is grouped
into **four missions**, each a single autonomous agent run in its own worktree:

| Mission | Linear | Scope | Points | Parallel? |
| --- | --- | --- | --- | --- |
| **A — Auth, Settings & User mgmt** | BAN-205 | BAN-200 (done), BAN-60, BAN-204 | ~6 | ✅ parallel |
| **B — Resource CRUD (templatable)** | BAN-206 | BAN-61, BAN-63 | ~10 | ✅ parallel |
| **C — Complex flows** | BAN-207 | BAN-62, BAN-64, BAN-65 | ~14 | ⛔ sequential, human-reviewed |
| **D — Public + dashboard + overlay** | BAN-208 | BAN-66 (done), BAN-67, BAN-179 | ~8 | ✅ parallel |

A, B, and D have no shared files and can run concurrently. C touches money and
legally-binding flows (`CLAUDE.md` §4) and runs **sequentially with a human in
the loop on every sub-issue** — it is explicitly *not* condensed into a
fire-and-forget run.

### Linear cleanup already applied (2026-05-29)

- Created mission parent issues **BAN-205/206/207/208** under BAN-11 (Phase 6).
- Re-parented the surviving leaf tickets to their mission.
- Closed four duplicate tickets as **Duplicate**: BAN-59→BAN-200,
  BAN-201→BAN-61, BAN-202→BAN-63, BAN-203→BAN-65.

---

## 3. The agent loop (per mission)

Each mission runs the same loop. The verification gate is what makes
autonomy safe.

1. **Spin up a worktree** off `feat/modernization` (see Risk R1):
   `git worktree add ../rentcar-mission-b feat/modernization`.
2. **Read the contract**: `CLAUDE.md`, the mission's Linear issue, and the
   matching `docs/test-catalogue.md` rows for the controllers in scope.
3. **Baseline**: run `php artisan test` — must be green before any change.
4. **Port one page group** following the BAN-11 per-group checklist:
   - React Page under `resources/js/Pages/...` (shadcn/ui primitives, JSX — **no TypeScript**, `CLAUDE.md` §1).
   - Forms via `useZodForm` (react-hook-form + zod); server validation surfaces into fields.
   - Switch the controller to `Inertia::render(...)`.
   - Add Vitest tests for non-trivial component logic.
5. **Verify (the gate)**: `php artisan test` (assert Inertia props via
   `Inertia::assertPropValue`) **and** `npm run test` both green. If red, fix
   before continuing — never advance on a red suite.
6. **Keep the Blade file** until a human smoke-tests the page; delete it in a
   separate cleanup commit on the same branch.
7. **Commit atomically** per `CLAUDE.md` §2: `feat(BAN-N): port <group> to Inertia/React`. Do not squash.
8. **Open a small PR** (<~400 lines diff). Repeat for the next group.

Sub-agents within a mission: one agent ports, a second reviews the diff against
`CLAUDE.md` §4 (no route/field/validation-message changes) before the PR opens.

---

## 4. Sequencing

```
Week 1   ┌─ Mission A (Auth/Settings) ──┐
         ├─ Mission B (Resource CRUD) ──┤  run in parallel worktrees
         └─ Mission D (Public/Dash) ────┘
Week 2   └─ Mission C (Booking/TVA/Agreement) ── sequential, reviewed ──┘
            then: Phase 6 exit gate (no jQuery/Alpine; views/ only app.blade + email/pdf)
Week 3   Phase 7 — cleanup + perf  (human-gated; see §6)
Week 4   Phase 8 — merge → tag → deploy → monitor  (human-gated)
```

Mission B should port **one reference resource first** (e.g. Vehicle), get it
green, then template the remaining ~11 resources from that pattern — this is
where the agentic leverage is highest.

---

## 5. Phase 6 exit gate (unchanged from BAN-11)

- `resources/views/` contains only `app.blade.php` + email/PDF Blade templates.
- `resources/js/` has zero imports of jQuery or Alpine.
- All PHP feature tests green; Vitest suite green.
- `jq-signature` removed (still in `package.json` today — drops in Phase 7 BAN-70).

---

## 6. Phases 7–8: NOT fully condensed

These stay human-gated by design:

- **Phase 7** perf fixes (BAN-69) depend on a re-run audit (BAN-68) producing
  *measured* deltas — an agent can implement a named fix but shouldn't pick
  which to make without the audit. Dead-code deletion (BAN-199) and dep removal
  (BAN-70) are safely agent-doable behind the test suite.
- **Phase 8** is merge/tag/deploy/monitor (BAN-73–78, 180–182) — requires
  human sign-off and GitHub Environment secrets, which agents must not touch.

---

## 7. Risks & open items

- **R1 — Branch divergence (blocker).** The migration commits landed on
  `dev`, but `CLAUDE.md` §2 mandates `feat/modernization`, and that branch is
  currently *stale* (older than `dev`). Resolve which branch is canonical
  **before** spinning up worktrees, or every mission inherits the ambiguity.
- **R2 — reCAPTCHA disabled in prod (correctness).** Per BAN-204, the auth port
  (BAN-200) merged with captcha validation skipped; operators must keep
  `google_recaptcha=off` until BAN-204 ships. Mission A closes this.
- **R3 — Phase 1 not truly closed.** BAN-188–195 (per-controller ≥80% coverage)
  and BAN-195 (raise CI gate 50%→80%) are still in Backlog; the CI coverage gate
  is at 50%, below the documented 80% target. Close these to restore the safety net's strength before relying on it for autonomous porting.
- **R4 — Linear ahead of code.** BAN-66/BAN-200 show Done but their React pages
  aren't in the `dev` working tree (they're on unmerged branches). Confirm those
  branches land on the canonical branch before counting them complete.
- **R5 — Stale ticket detail.** BAN-11's description still says "TypeScript";
  `CLAUDE.md` §1 mandates plain JSX. Agents should follow `CLAUDE.md`.

---

## 8. One-line mission kickoffs

- **A:** "In a worktree off the canonical branch, port Settings + user-management pages (BAN-60) and wire reCAPTCHA back on (BAN-204), gated by Auth/User/Setting feature tests + Vitest. No route/field/message changes."
- **B:** "Port Vehicle CRUD as the reference resource, then template VehicleType/Driver/Place/Option/Addon and Inspection/Expense/Reminder (BAN-61, BAN-63), gated by the Phase 1 core-domain CRUD tests."
- **C:** "Sequentially port Booking → TVA → Rental-agreement/signer (BAN-62/64/65), human-reviewed per sub-issue; signature round-trip + booking pricing tests must stay green; manual smoke test before any Blade deletion."
- **D:** "Port the public landing + booking flow (BAN-67) and fold client-specific views into the DirectOnderweg overlay (BAN-179), gated by HomeController + public-booking tests; APP_CLIENT=directonderweg behavior unchanged."
