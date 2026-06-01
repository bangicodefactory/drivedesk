export const meta = {
  name: 'phase6-port',
  description: 'Phase 6: port ONE Blade page group to Inertia/React/shadcn per run. Select the group with args (e.g. args="addon").',
  whenToUse: 'Port a single remaining Phase 6 group. Pass the group key as args: addon | inspection | expense | reminder | public | overlay | recaptcha (or "canary" for the Vehicle reference, or "list" to print the registry). One group per invocation by design — run, verify+merge its PR, then run the next.',
  phases: [
    { title: 'Baseline' },
    { title: 'Port' },
    { title: 'Open PR' },
    { title: 'Compliance' },
    { title: 'Ready' },
  ],
}

/*
 * v4 — ONE GROUP PER INVOCATION.
 *
 * Why: running all groups in one run (v3) reliably aborted partway when the
 * session's tool-IO degraded, AND overlapping runs corrupted the shared MySQL
 * DB. Per the user's instruction, each invocation now ports EXACTLY ONE group,
 * selected by `args` (a group key). The orchestrator cleans the tree, kills
 * orphan PHP, re-seeds + verifies a green baseline BEFORE each run, then
 * verifies + merges the single PR AFTER, then runs the next group. No two runs
 * ever overlap; the blast radius per run is one group.
 *
 * Flow per run: baseline gate -> port the one group -> draft PR ->
 * migration-reviewer CLAUDE.md s4/s5 gate -> mark ready. A HUMAN (or the
 * orchestrator, per standing policy) merges; the workflow never merges.
 */

const BASE = 'origin/dev'

const HARD_RULES =
  'CLAUDE.md is binding. Do NOT change: routes/URLs/HTTP verbs/route names, ' +
  'form field names, validation messages (any of the 14 locales), response ' +
  'status codes, DB schema, permission strings, or translation keys (adding ' +
  'keys is allowed). Plain JSX only — NEVER TypeScript. Keep every Blade file ' +
  'in place (deletion is a separate post-smoke commit). Forms use useZodForm ' +
  '(react-hook-form + zod); server validation stays authoritative and surfaces ' +
  'via Inertia errors with setError. i18n comes through Inertia shared props — ' +
  'never duplicate lang/ strings into JS. shadcn primitives live in ' +
  'resources/js/components/ui and must not be edited directly. Only convert ' +
  'controller methods that currently `return view(...)` and are actively routed; ' +
  'preserve JSON/AJAX endpoints and commented-out routes EXACTLY.'

const REFERENCE =
  'Use the already-merged Vehicle/VehicleType/Driver/Option/Place ports on dev ' +
  'as your TEMPLATE: resources/js/Pages/{Vehicle,VehicleType,Driver,Option,Place}/' +
  '{Index,Create,Edit,Show}.jsx; the conditional render pattern in their ' +
  "controllers (GET methods do `if (config('app.inertia_enabled')) return " +
  "Inertia::render('<Page>', $props);` and KEEP the original `return view(...)` " +
  'as the fallback — this matches the CI Blade-path tests); and ' +
  'tests/Feature/InertiaVehicleTest.php for the assert-Inertia test style. Reuse ' +
  'useZodForm and the shadcn primitives those pages use. Keep prop names ' +
  'identical to the Blade compact() vars.'

const FLAKY_NOTE =
  'NOTE: this session has intermittent tool-output capture failures. If a ' +
  'command returns empty output unexpectedly, retry it 2-3 times (and prefer ' +
  'writing results to a file then Reading it) before concluding anything. Never ' +
  'report a gate as passed/failed unless you actually observed the result. Use ' +
  'POSIX/bash syntax only (PowerShell cmdlets are denied). Never git clean or ' +
  'force-push. Stage only your own files by explicit path (never git add -A).'

const BASELINE_RESULT = {
  type: 'object', additionalProperties: false,
  properties: {
    green: { type: 'boolean' },
    passed: { type: 'number' },
    failed: { type: 'number' },
    summary: { type: 'string' },
  },
  required: ['green', 'summary'],
}

const PORT_RESULT = {
  type: 'object', additionalProperties: false,
  properties: {
    branch: { type: 'string' },
    committed: { type: 'boolean' },
    pushed: { type: 'boolean' },
    phpGreen: { type: 'boolean', description: 'php artisan test green AFTER the port' },
    vitestGreen: { type: 'boolean', description: 'npm run test green AFTER the port' },
    pagesAdded: { type: 'array', items: { type: 'string' } },
    controllerMethodsSwitched: { type: 'array', items: { type: 'string' } },
    summary: { type: 'string' },
    blockers: { type: 'string', description: 'empty string if none' },
  },
  required: ['branch', 'committed', 'pushed', 'phpGreen', 'vitestGreen', 'summary', 'blockers'],
}

const PR_RESULT = {
  type: 'object', additionalProperties: false,
  properties: {
    opened: { type: 'boolean' },
    prRef: { type: 'string', description: 'e.g. #123, empty if not opened' },
    prUrl: { type: 'string' },
    note: { type: 'string' },
  },
  required: ['opened', 'prRef', 'prUrl', 'note'],
}

const COMPLIANCE = {
  type: 'object', additionalProperties: false,
  properties: {
    verdict: { type: 'string', enum: ['PASS', 'FAIL'] },
    issues: {
      type: 'array',
      items: {
        type: 'object', additionalProperties: false,
        properties: {
          file: { type: 'string' }, line: { type: 'string' },
          rule: { type: 'string' }, detail: { type: 'string' },
        },
        required: ['file', 'rule', 'detail'],
      },
    },
    summary: { type: 'string' },
  },
  required: ['verdict', 'issues', 'summary'],
}

const READY = {
  type: 'object', additionalProperties: false,
  properties: { markedReady: { type: 'boolean' }, note: { type: 'string' } },
  required: ['markedReady', 'note'],
}

// Registry of every group, keyed for single-group selection via args.
// Merged already (NOT here): vehicle #50, vehicletype #52, driver #53,
// option #54, place #55. Each remaining run = exactly one of these.
const REGISTRY = {
  canary: { ban: 'BAN-61', name: 'Vehicle CRUD', pages: 'Vehicle', blade: 'vehicle',
    note: 'Reference resource (already merged as #50) — only re-port if explicitly rebuilding the template.' },

  addon: { ban: 'BAN-61', name: 'Addon CRUD', pages: 'Addon', blade: 'addon',
    note: 'Preserve the addon/rate/calculation and addon/rate/reduction JSON endpoints EXACTLY.' },

  inspection: { ban: 'BAN-63', name: 'Inspection + InspectionType', pages: 'Inspection,InspectionType', blade: 'inspection,inspection_type',
    note: 'Two resources in one group; mirror the template for each. inspection/show.blade.php exists — port Show too if the route renders it.' },

  expense: { ban: 'BAN-63', name: 'Expense + ExpenseType', pages: 'Expense,ExpenseType', blade: 'expense,expense_type' },

  reminder: { ban: 'BAN-63', name: 'Reminder + ReminderType', pages: 'Reminder,ReminderType', blade: 'reminder,reminder_type',
    note: 'Several reminder routes are commented out in routes/web.php and there are many JSON/AJAX reminder endpoints; ONLY port the actively-routed view-rendering methods and preserve everything else verbatim.' },

  public: { ban: 'BAN-67', name: 'Public landing + public booking flow', pages: 'RequestBooking', blade: 'booking_requests,client', public: true,
    note: 'Customer-facing public site (resources/views/client/* is a full marketing site: home, pages/{contact,search}, pages/home/* partials, layouts/*) plus the public booking flow (booking_requests/*). Port the actively-routed pages; this is the riskiest group — if scope is unclear, port what is clearly routed and note the rest in blockers rather than guessing.' },

  overlay: { ban: 'BAN-179', name: 'DirectOnderweg client overlay', pages: 'client/*', blade: 'client', overlay: true,
    note: 'CLIENT-OVERLAY MOVE, not a visual port. Relocate the DirectOnderweg-specific client/* Blade views and config/default_terms.php under app/Clients/DirectOnderweg/ behind APP_CLIENT=directonderweg with ZERO behavior change. ClientServiceProvider already exists (app/Providers/ClientServiceProvider.php, registered in config/app.php) and app/Clients/DirectOnderweg/{Providers,Services}/ already exist. No restyle, no route/field/validation/status/schema/permission/translation-key changes.' },

  recaptcha: { ban: 'BAN-204', name: 'reCAPTCHA on React auth pages', pages: 'Auth', recaptcha: true,
    note: 'The React Auth pages (Login/Register/ForgotPassword) ALREADY EXIST on dev from the Breeze/Inertia stack — do NOT recreate them. Wire reCAPTCHA into those existing pages and re-enable the server-side captcha validation to match the original Blade behavior (package: anhskohbo/no-captcha). Keep google_recaptcha=off as the default. No route/field/validation-message/status changes.' },
}

const REMAINING = ['addon', 'inspection', 'expense', 'reminder', 'public', 'overlay', 'recaptcha']

function branchName(group) {
  const slug = group.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '').slice(0, 40)
  return group.ban.toLowerCase() + '-port-' + slug
}

// Port one group end to end IN THE MAIN WORKING TREE: port -> draft PR ->
// compliance gate -> mark ready. No worktree isolation, no dep install.
async function portGroup(group) {
  const branch = branchName(group)
  const kind = group.overlay ? group.note + ' ' + HARD_RULES
    : group.recaptcha ? group.note + ' ' + HARD_RULES
    : 'Port the ' + group.name + ' page group (' + group.pages + ') from Blade to ' +
      'Inertia/React/shadcn. Source Blade lives under resources/views/' +
      (group.blade || group.pages.toLowerCase()) + '/. Create the React pages under ' +
      'resources/js/Pages/' + group.pages.split(',').join('/ and resources/js/Pages/') +
      '/ mirroring the Blade folder 1:1, and switch the view-rendering controller ' +
      'method(s) to the conditional Inertia::render pattern. ' + (group.note ? group.note + ' ' : '') +
      HARD_RULES + ' ' + REFERENCE

  const port = await agent(
    'You are porting ' + group.ban + ' (' + group.name + ') in the MAIN working tree (deps + ' +
    'storage dirs already present — do NOT run composer install or npm ci). ' + FLAKY_NOTE + '\n\n' +
    'STEPS:\n' +
    '1. Start clean on a fresh branch off ' + BASE + ': `git fetch origin && git switch -c ' + branch + ' ' + BASE +
    '`. If tracked files are dirty, `git checkout -- .` first then retry. If the branch already exists, ' +
    '`git switch ' + branch + ' && git reset --hard ' + BASE + '`. NEVER git clean — untracked items ' +
    '(.claude/, docs/, public/hot, .env) MUST be preserved.\n' +
    '2. The dev baseline was already verified GREEN by the orchestrator; you need not re-run the full baseline.\n' +
    '3. Read the contract + source: CLAUDE.md (s4/s5), routes/web.php for this resource, the Blade views, ' +
    'the controller(s), the relevant docs/test-catalogue.md rows, and the template ports named below.\n' +
    '4. ' + kind + '\n' +
    '5. Add Vitest tests for non-trivial component logic and a Feature test asserting the Inertia ' +
    'component + props (mirror tests/Feature/InertiaVehicleTest.php).\n' +
    '6. VERIFY GATE (mandatory): run `php artisan test` AND `npm run test` — BOTH must be green. Fix until ' +
    'green. If you cannot, set the matching *Green flag false, record why in blockers, do NOT commit, STOP.\n' +
    '7. Only if both gates are green: stage ONLY the files you created/modified for THIS group by explicit ' +
    'path (`git add resources/js/Pages/<Dir> app/Http/Controllers/<X>Controller.php tests/...`) — NEVER ' +
    'git add -A. Leave any unrelated untracked file unstaged. Commit `feat(' + group.ban + '): port ' +
    group.name + ' to Inertia/React` (keep Blade files) and push `git push -u origin ' + branch + '`. ' +
    'Remove any scratch/temp files you created before finishing.\n\n' +
    'Return PORT_RESULT. blockers="" only if everything above succeeded.',
    { label: 'port:' + group.pages, phase: 'Port', agentType: 'inertia-porter', schema: PORT_RESULT },
  )

  const portOk = port && port.committed && port.pushed && port.phpGreen && port.vitestGreen && !port.blockers
  if (!portOk) return { group: group.name, ban: group.ban, branch, status: 'PORT_BLOCKED', port }

  const pr = await agent(
    'Open a DRAFT pull request from branch `' + branch + '` into dev, titled ' +
    '"feat(' + group.ban + '): port ' + group.name + ' to Inertia/React". Body: link ' + group.ban +
    ', list the React pages added and controller methods switched, note Blade files are kept for ' +
    'human smoke-testing. Use `gh pr create --draft --base dev --head ' + branch + ' --title "..." --body "..."`. ' +
    'Do NOT merge. ' + FLAKY_NOTE + ' Return PR_RESULT (read the ref + URL from the gh output).',
    { label: 'pr:' + group.pages, phase: 'Open PR', schema: PR_RESULT },
  )
  if (!pr || !pr.opened) return { group: group.name, ban: group.ban, branch, status: 'PR_OPEN_FAILED', port, pr }

  const compliance = await agent(
    'CLAUDE.md s4/s5 COMPLIANCE GATE for PR ' + pr.prRef + ' (' + group.ban + ', ' + group.name + '). ' +
    'Inspect the diff: `git fetch origin && git diff ' + BASE + '...' + branch + '`. Run every check in your ' +
    'reviewer checklist (routes/verbs/names unchanged, form field names unchanged, validation messages ' +
    'unchanged across all 14 locales, status codes unchanged, no schema migration, NO TypeScript, Blade ' +
    'NOT deleted, no orthogonal refactor, permissions intact, PHP + Vitest reported green). ' + FLAKY_NOTE + ' ' +
    'Return COMPLIANCE: verdict PASS only if every check holds, else FAIL with file/line/rule per issue.',
    { label: 'review:' + group.pages, phase: 'Compliance', agentType: 'migration-reviewer', schema: COMPLIANCE },
  )
  if (!compliance || compliance.verdict !== 'PASS') {
    return { group: group.name, ban: group.ban, branch, status: 'REWORK_COMPLIANCE', prRef: pr.prRef, prUrl: pr.prUrl, port, compliance }
  }

  const ready = await agent(
    'Mark PR ' + pr.prRef + ' ready for review: `gh pr ready ' + pr.prRef + '`. Do NOT merge — a human merges. ' +
    FLAKY_NOTE + ' Return READY.',
    { label: 'ready:' + group.pages, phase: 'Ready', schema: READY },
  )
  return {
    group: group.name, ban: group.ban, branch,
    status: ready && ready.markedReady ? 'PR_READY_AWAITING_HUMAN_MERGE' : 'PR_READY_MARK_FAILED',
    prRef: pr.prRef, prUrl: pr.prUrl, port, compliance, ready,
  }
}

// ----------------------------- run (ONE group) -----------------------------
const sel = (typeof args === 'string' ? args : (args && args.group) || '').trim().toLowerCase()

if (sel === 'list' || !sel) {
  return {
    phase: '6 -- one-group-per-run',
    usage: 'Invoke with args set to a single group key.',
    remaining: REMAINING,
    allKeys: Object.keys(REGISTRY),
    note: 'Example: run this workflow with args="addon". Vehicle/VehicleType/Driver/Option/Place are already merged.',
  }
}

const group = REGISTRY[sel]
if (!group) {
  return {
    phase: '6 -- one-group-per-run ERROR',
    error: 'Unknown group key: "' + sel + '".',
    validKeys: Object.keys(REGISTRY),
    remaining: REMAINING,
  }
}

phase('Baseline')
log('ONE-GROUP RUN: ' + sel + ' (' + group.ban + ' — ' + group.name + '). Verifying baseline, then porting just this group.')
const baseline = await agent(
  'Verify the Phase 6 baseline in the MAIN working tree. Run `git fetch origin && git switch -C ' +
  'ban-phase6-baseline ' + BASE + '` (preserve untracked files; never git clean), then `php artisan test`. ' +
  FLAKY_NOTE + ' Report BASELINE_RESULT with green=true ONLY if the whole suite passes (report ' +
  'passed/failed counts). Make NO source changes.',
  { label: 'baseline', phase: 'Baseline', schema: BASELINE_RESULT },
)
if (!baseline || !baseline.green) {
  return {
    phase: '6 -- one-group run ABORTED at baseline',
    base: BASE, group: sel,
    baseline,
    note: 'Baseline not observed green in the main tree; nothing ported. Have the orchestrator kill orphan ' +
      'php.exe, re-seed the DB (migrate:fresh --seed), confirm a green baseline serially, then re-run with ' +
      'args="' + sel + '".',
  }
}

log('Baseline GREEN (' + (baseline.passed || '?') + ' passed). Porting ' + sel + ' only.')
const result = await portGroup(group)
return {
  phase: '6 -- one-group run (' + sel + ')',
  base: BASE,
  group: sel,
  result,
  remainingAfterThis: REMAINING.filter((k) => k !== sel),
  note:
    result.status === 'PR_READY_AWAITING_HUMAN_MERGE'
      ? 'PR ' + (result.prRef || '') + ' is ready. Orchestrator: verify CI + diff, then merge (merge commit, ' +
        'no squash). Re-seed the DB (the main-tree test run wiped it), clean the tree, then run the next ' +
        'group one at a time.'
      : 'Group ' + sel + ' did not reach ready (' + result.status + '). See result.port.blockers / ' +
        'result.compliance. Fix or rework, then re-run args="' + sel + '".',
}
