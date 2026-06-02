/**
 * Dynamic workflow: Phase 7 -- Cleanup + performance fixes
 * ------------------------------------------------------------------
 * Linear:  BAN-12 (parent). Leaves: BAN-199, BAN-68, BAN-69, BAN-70,
 *          BAN-71, BAN-72, BAN-182.
 * Entry gate: Phase 6 exit gate met (all ports merged, Blade removed,
 *             suite + Vitest green).
 * Canonical branch: dev.
 * Gates (per user decision):
 *   - Dead-code deletion & dep removal run autonomously behind the test suite.
 *   - PERF FIXES are human-picked: the agent re-runs the audit and proposes,
 *     but a human approves WHICH findings to fix (CLAUDE.md section 7 "audit,
 *     don't optimize"). Each fix is its own PR.
 *   - Each PR is reviewed with the built-in /review command and the MUST-FIX
 *     findings are IMPLEMENTED automatically (loop until clean).
 *   - HUMAN approval before EVERY merge.
 * This is a sequential PIPELINE: a perf regression mid-way must be caught
 * before the next step, so steps do not fan out.
 */

const BASE = "dev";
const MAX_REVIEW_ROUNDS = 3;

// Run /review on the PR and implement MUST-FIX findings, looping until clean.
async function reviewAndFix(ban, title, prRef) {
  const rounds = [];
  for (let round = 1; round <= MAX_REVIEW_ROUNDS; round++) {
    const review = await agent("inertia-porter", {
      base: BASE,
      instructions:
        "Run the /review " + prRef + " slash command on PR " + prRef + " for " + ban +
        " (" + title + "). Return findings verbatim, each tagged MUST-FIX or NIT, " +
        "or exactly 'REVIEW_CLEAN'.",
      tools: ["Bash", "Read", "Grep"],
    });
    rounds.push({ round, review });
    if (review.includes("REVIEW_CLEAN") || !review.includes("MUST-FIX")) {
      return { status: "REVIEW_CLEAN", rounds };
    }
    await agent("inertia-porter", {
      base: BASE,
      instructions:
        "Implement every MUST-FIX item from this /review of " + title + ":\n" + review +
        "\nKeep CLAUDE.md section 4 invariants (no route/field/message/schema change). " +
        "Re-run php artisan test (+ npm run test if frontend touched) -- green. Commit " +
        "atomically (fix(" + ban + "): address /review findings) and push to the PR branch. " +
        "Do NOT merge.",
      tools: ["Read", "Edit", "Bash"],
    });
  }
  return { status: "REVIEW_BUDGET_EXHAUSTED", rounds };
}

async function step(ban, title, instructions, tools, gate) {
  // 1. Do the work on a branch off dev; commit; do not merge.
  const work = await agent("inertia-porter", { // same toolset; cleanup, not porting
    base: BASE,
    instructions: "[" + ban + "] " + title + ". " + instructions + " Baseline green first; " +
      "commit atomically; do NOT squash; do NOT merge -- return diff + test output.",
    tools,
  });
  // 2. Open a draft PR so /review has a target. Return ONLY the PR ref.
  const pr = await agent("inertia-porter", {
    base: BASE,
    instructions: "Open a DRAFT PR into " + BASE + " titled '" + ban + ": " + title + "'. " +
      "Link " + ban + ". Do NOT merge. Return ONLY the PR ref (e.g. #123).",
    tools: ["Bash"],
  });
  const prRef = pr.trim();
  // 3. /review + auto-fix loop.
  const reviewed = await reviewAndFix(ban, title, prRef);
  // 4. CLAUDE.md compliance gate.
  const review = await agent("migration-reviewer", {
    instructions: "Review PR " + prRef + " for " + ban + " (" + title + ") against CLAUDE.md. " +
      "Confirm: no schema change, no route/field/message change, tests green, only the " +
      "intended files touched. Return PASS or FAIL with file:line.",
    tools: ["Read", "Grep", "Glob", "Bash"],
  });
  const passed = review.includes("PASS") && reviewed.status === "REVIEW_CLEAN";
  // 5. If clean, mark ready; stop for human merge.
  if (passed) {
    await agent("inertia-porter", {
      base: BASE,
      instructions: "Mark PR " + prRef + " ready for review (remove draft). Do NOT merge.",
      tools: ["Bash"],
    });
  }
  return { ban, title, prRef, passed, gate, reviewed, review, work };
}

const results = [];

// 0. Delete SaaS payment/subscription dead code (BAN-199, plan section 7.0).
results.push(await step("BAN-199",
  "Delete payment/subscription dead code + packages",
  "Delete PaymentController, SubscriptionController; Subscription/PackageTransaction/" +
  "Coupon models; resources/views/subscription/ and settings/payment.blade.php; " +
  "config/paypal.php. Remove srmklive/paypal, stripe/stripe-php, mashape/unirest-php " +
  "from composer.json. Remove subscription/coupon permission rows from the seeder. " +
  "LEAVE the DB tables in place (schema is frozen -- dropped post-Phase 8).",
  ["Read", "Edit", "Bash"], "human-merge"));

// 1. Re-run the perf audit vs the Phase 0 baseline (BAN-68). REPORT ONLY.
results.push(await step("BAN-68",
  "Re-run perf audit; update docs/perf-audit.md with Phase 6 deltas",
  "Re-profile the 10 audited endpoints with Telescope/Debugbar/slow-query log. " +
  "Append a prioritized findings list with reproduction steps and rough estimates. " +
  "Do NOT fix anything in this step (CLAUDE.md section 7).",
  ["Read", "Edit", "Bash"], "human-merge"));

// --- HUMAN GATE: pick which of the top findings to fix (BAN-69). ---
// The workflow returns the audit; the user chooses the fixes, then re-invokes
// with approvedFixes populated. Each approved fix becomes its own PR.
const approvedFixes = (workflowInput && workflowInput.approvedFixes) || [];
for (const fix of approvedFixes) {
  results.push(await step("BAN-69",
    "Perf fix: " + fix,
    "Implement ONLY this approved finding (eager-load / index / cache / queue). " +
    "A test must depend on the change. One concern per PR.",
    ["Read", "Edit", "Bash"], "human-merge"));
}

// 3. Remove dead deps (BAN-70): laravel-mix, alpinejs, jq-signature, jquery.
results.push(await step("BAN-70",
  "Remove dead deps (laravel-mix, alpinejs, jq-signature, jquery)",
  "Remove from package.json + lockfile; confirm no imports remain (grep). " +
  "npm run build must still succeed.",
  ["Read", "Edit", "Bash"], "human-merge"));

// 4. Drop the INERTIA_ENABLED feature flag -- final cutover (BAN-71).
results.push(await step("BAN-71",
  "Drop INERTIA_ENABLED flag (final cutover)",
  "Remove the flag and the Blade-fallback branches; React is now the only path. " +
  "Full suite + Vitest green.",
  ["Read", "Edit", "Bash"], "human-merge"));

// 5. Rewrite README for the post-migration stack only (BAN-72).
results.push(await step("BAN-72",
  "Rewrite README.md to the post-migration stack",
  "Document Laravel 12 + Inertia/React 19 + Vite + Tailwind 4 + shadcn only; " +
  "drop Mix/Alpine/jQuery setup.",
  ["Read", "Edit", "Bash"], "human-merge"));

// 6. Stand up the staging GitHub Environment (BAN-182). SECRETS = human-only.
results.push({
  ban: "BAN-182", title: "Set up staging-directonderweg GitHub Environment",
  passed: null, gate: "HUMAN-ONLY",
  note: "Agents must NOT create environments or touch secrets (STRIPE/PAYPAL/DB/" +
    "APP_CLIENT). The workflow stops here and hands off to the user.",
});

return {
  phase: "7 -- Cleanup + perf",
  base: BASE,
  note: "Sequential pipeline. Perf fixes are human-picked from the re-run audit; " +
    "re-invoke with approvedFixes set. Every code step ends at an open PR awaiting " +
    "human merge. Secret/environment work is human-only.",
  results,
};
