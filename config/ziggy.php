<?php

/*
 * Ziggy route export (BAN-262).
 *
 * The full route list is inlined into every HTML response. It was ~23KB — about
 * 40% of the document — and included the rachidlaasri installer and updater
 * route groups. Those are correctly 404'd in production, so this was never an
 * exposure, but publishing a map of endpoints that do not exist is pure weight
 * on a page whose first paint already waits on a 143KB bundle.
 *
 * `except` is a name-prefix match. Ziggy honours `except` only when `only` is
 * absent, so do not define both.
 */
return [

    /*
     * There is deliberately no `groups` block.
     *
     * A `public` group once trimmed marketing pages to 7 routes to save ~22KB.
     * It shipped broken: @routes writes window.Ziggy once per *document*, and
     * Inertia moves between pages without a document load. Landing on / and
     * clicking "Log in" is a client-side visit, so the Login page rendered
     * against the 7-route list and route('password.request') threw. It worked
     * on reload, which made it look intermittent rather than systematic.
     *
     * The route list therefore has to cover everything reachable without a
     * document load. Since the login link leads into the whole admin, that is
     * effectively every route — per-page trimming cannot work here. `except`
     * below is still safe because it drops routes no JavaScript ever names.
     */

    'except' => [
        // Install/update wizards — guarded by InstallerGuard, never reachable
        // once the app is installed. Nothing in the SPA routes to them.
        'LaravelInstaller::*',
        'LaravelUpdater::*',

        // Temporary UI scaffolding (routes/web.php "UI COMPONENT TEST ROUTES"),
        // already flagged for removal before production in docs/test-catalogue.md.
        'ui.test.*',

        // Vendor endpoints the front end calls by literal path, not by name.
        'sign-pad::*',
        'debugbar.*',
        'telescope.*',
    ],

];
