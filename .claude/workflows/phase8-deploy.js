/**
 * Dynamic workflow: Phase 8 — Merge -> tag -> deploy -> monitor
 * ------------------------------------------------------------------
 * Linear:  BAN-13 (parent). Leaves: BAN-73, BAN-74, BAN-75, BAN-76,
 *          BAN-77, BAN-78, BAN-180.
 * Entry gate: Phase 7 exit gate met; stakeholders signed off on a staging
 *             environment running the migrated app.
 * Canonical branch: `dev` is the trunk now; promote dev -> main.
 *
 * Phase 8 is INTENTIONALLY NOT autonomous (your "gate deploy & secrets"
 * decision + plan section 6). Agents may PREPARE reversible artifacts (rebase,
 * draft PR, draft deploy.yml). Everything that ships -- merging, tagging,
 * creating GitHub Environments, setting secrets, deploying -- is HUMAN-ONLY.
 */

const BASE = "dev";
const results = [];

// dev is canonical; feat/modernization retired -> BAN-73 collapses to a no-op.
results.push({
  ban: "BAN-73", title: "Rebase feat/modernization on dev",
  status: "OBSOLETE",
  note: "feat/modernization is retired (dev is canonical, 128 commits ahead). " +
    "Close BAN-73 as obsolete or repurpose to 'archive feat/modernization branch'.",
});

// BAN-74: draft the dev -> main PR. Agent prepares; human reviews & merges.
results.push(await (async () => {
  const draft = await agent("migration-reviewer", {
    instructions:
      "Prepare the dev -> main promotion PR description: summarize the migration by " +
      "phase folder (not one blob), list smoke-test evidence from docs/migration-log.md, " +
      "and flag anything still red. Do NOT open or merge -- output the draft PR body.",
    tools: ["Read", "Grep", "Bash"],
  });
  return { ban: "BAN-74", title: "Draft PR dev -> main", status: "DRAFT_FOR_HUMAN", draft };
})());

// BAN-75: merge dev / promote main. HUMAN-ONLY.
results.push({
  ban: "BAN-75", title: "Merge to dev / promote dev -> main",
  status: "HUMAN-ONLY",
  note: "Requires branch-protection review + linear history; you merge.",
});

// BAN-180: author deploy.yml (per-environment, tag-triggered). Agent drafts; no secrets.
results.push(await (async () => {
  await agent("inertia-porter", {
    base: BASE,
    instructions:
      "Draft .github/workflows/deploy.yml: per-environment (production-<client>, " +
      "staging-<client>), tag-triggered (v*), reads secrets from the GitHub Environment " +
      "(NEVER inline them). Reference APP_CLIENT. Commit on a branch. Add NO real secret values.",
    tools: ["Read", "Write", "Bash"],
  });
  const pr = await agent("inertia-porter", {
    base: BASE,
    instructions: "Open a DRAFT PR into " + BASE + " titled 'BAN-180: author deploy.yml'. " +
      "Do NOT merge. Return ONLY the PR ref.",
    tools: ["Bash"],
  });
  const prRef = pr.trim();
  // /review the deploy workflow and implement MUST-FIX findings (loop, max 3).
  const rounds = [];
  for (let r = 1; r <= 3; r++) {
    const review = await agent("inertia-porter", {
      base: BASE,
      instructions: "Run the /review " + prRef + " slash command on PR " + prRef +
        " (deploy.yml). Pay special attention to secret handling and least-privilege. " +
        "Return findings tagged MUST-FIX or NIT, or exactly 'REVIEW_CLEAN'. " +
        "Confirm NO secret values are inlined.",
      tools: ["Bash", "Read", "Grep"],
    });
    rounds.push({ r, review });
    if (review.includes("REVIEW_CLEAN") || !review.includes("MUST-FIX")) break;
    await agent("inertia-porter", {
      base: BASE,
      instructions: "Implement the MUST-FIX items from this /review:\n" + review +
        "\nNever inline secrets. Commit and push to the PR branch. Do NOT merge.",
      tools: ["Read", "Edit", "Bash"],
    });
  }
  return { ban: "BAN-180", title: "Author deploy.yml",
    status: "PR_OPEN_AWAITING_HUMAN_MERGE", prRef, reviewRounds: rounds };
})());

// BAN-76 / BAN-77 / BAN-78: environments, secrets, tag, deploy, monitor. HUMAN-ONLY.
results.push({ ban: "BAN-76", title: "Create production-directonderweg Environment + secrets",
  status: "HUMAN-ONLY", note: "GitHub Environment + secrets are operator-only (CLAUDE.md section 10.3.5)." });
results.push({ ban: "BAN-77", title: "Tag v2.0.0 on main + deploy to production-directonderweg",
  status: "HUMAN-ONLY", note: "Tagging a release and deploying is an irreversible ship action -- you do it." });
results.push({ ban: "BAN-78", title: "1-week monitoring + sign-off",
  status: "HUMAN-ONLY", note: "Watch logs/Telescope/Sentry for a week; can be scheduled as a daily check." });

return {
  phase: "8 -- Merge + deploy",
  base: BASE,
  note: "Agents only PREPARE reversible artifacts (rebase notes, draft PR body, draft " +
    "deploy.yml). All ship/secret/environment actions are HUMAN-ONLY by design.",
  results,
};
