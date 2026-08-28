# Design: split `rentcar` into `rentcar` (directonderweg) and `drivedesk` (product)

Date: 2026-08-28. Status: approved, executed the same day.

## Problem

One repository (`bangicodefactory/rentcar`) served two clients through the
`APP_CLIENT` multi-client mechanism: **directonderweg** — the paying customer
at `directonderweg.com`, tagged `vX.Y.Z` — and **drivedesk** — our own product
demo at `drivedesk.ma`, tagged `drivedesk/vX.Y.Z`. DriveDesk's marketing
gateway, demo seeding, `ary` locale, sales decks/video and deploy runbook lived
next to the customer's code, and every DriveDesk change ran through the
customer's CI and history.

## Decision

**Full fork.** `bangicodefactory/drivedesk` (this repo) is a mirror of the
whole history and becomes the home of the DriveDesk product: the app, the demo
gateway, the sales collateral, and the `drivedesk.ma` deployment. `rentcar`
keeps directonderweg only.

Both repos keep the generic multi-client mechanism (`ClientServiceProvider`,
feature flags, `config/clients/`) so a new client can be onboarded in either;
each simply ships one client by default.

Accepted trade-off: the two codebases diverge from day one. A core bug fix has
to be applied to both by hand. This was chosen over "collateral-only" (which
would have kept drivedesk code in the customer's repo) and "fork with
upstream" (a shared-fix path that gets harder with every divergence).

Hard constraint: `drivedesk.ma` is verified deploying from this repo **before**
anything is deleted from `rentcar`. directonderweg production is never touched.

## Shape of this repo after the split

- `APP_CLIENT` defaults to `drivedesk` everywhere (`config/app.php`,
  `.env.example`, provider/command fallbacks, CI matrix, `deploy.yml`).
- A bare `vX.Y.Z` tag deploys to `production-drivedesk`; the
  `<client>/vX.Y.Z` form still works for future clients.
- directonderweg's client config, `app/Clients/DirectOnderweg/`, per-env
  `.env` examples, deploy runbooks and CODEOWNERS lines are gone.
- The four Blade partials that only existed in the DirectOnderweg view overlay
  but are hard-`@include`d by the core storefront layout were promoted into
  core, so the storefront works with no overlay.
- Tests: `WithClient::asClient()` falls back to
  `tests/Fixtures/clients/<name>.php`; route-level suites use `asClient('acme')`
  as their neutral non-demo tenant (same flag set they were calibrated against).
  `ClientFeatureMatrixTest` asserts only what drivedesk actually runs.
- `docs/deploy.md` (was `deploy-drivedesk-ma.md`) is self-contained, with the
  host quirks (no Redis, SMTP for the demo form, LiteSpeed bridge docroot) as
  appendices.
- The demo-request inbox is `config('client.demo_request_to')` instead of a
  controller constant.

## Cutover of drivedesk.ma

1. Recreate the `production-drivedesk` GitHub Environment here (secrets are
   write-only on GitHub; every value is re-entered by hand).
2. Add the host's deploy key as a read-only deploy key on this repo; on the
   host, `git remote set-url origin` in `~/drivedesk`.
3. Tag `v1.0.38` (continues the drivedesk numbering, now bare) and watch the
   Deploy run: CI gate → SSH deploy → `/login` health check → manual smoke of
   `/`, `/fr` `/en` `/ar`, the demo form, and the admin demo-requests page.
4. Only then: remove DriveDesk from `rentcar` and delete its
   `production-drivedesk` environment and `drivedesk/*` tags there.

## Out of scope

- Rewriting history to drop the sales binaries from `rentcar`.
- Translating the `dg_*` gateway copy into fr/en (still a known content gap).
- Any behaviour change for either client beyond what the split requires.
