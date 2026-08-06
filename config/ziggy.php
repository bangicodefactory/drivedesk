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
     * Public marketing pages need a handful of routes, not the app's ~500. The
     * shell emits this group instead of the full list when the page is one of
     * the indexable ones (see App\Support\Seo and app.blade.php), which drops
     * ~22KB from the document a first-time visitor downloads.
     *
     * Over-filtering breaks route() at runtime and fails silently until someone
     * clicks — SeoMetadataTest asserts each name below is actually present.
     */
    'groups' => [
        'public' => [
            'login',                 // nav + "already have credentials" links
            'demo.request',          // the B2B gateway's demo form
            'client.home',           // storefront landing
            'client.details',        // storefront vehicle detail
            'contact',
            'search',
            'newsletter.subscribe',
        ],
    ],

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
