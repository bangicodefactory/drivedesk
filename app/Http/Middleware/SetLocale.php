<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Locales the app can actually serve.
     *
     * 'ary' = Moroccan Arabic (Darija). **No client defaults to it any more**
     * -- drivedesk did until BAN-330 -- and it is in no config, no public URL
     * set and neither language switcher. It stays here anyway, deliberately:
     * BAN-330 was a change of default, not a withdrawal, and an account that
     * already stores 'ary' must keep working. Removing it from this list is
     * what would break that promise;
     * LocaleResolutionTest::test_explicit_ary_language_switch_is_still_accepted
     * is the only thing standing in the way, so read this before deleting it.
     *
     * Note also that a client may list a locale in supported_locales without it
     * being servable here (the acme fixture lists `nl`), which is why
     * App\Support\Locales intersects the two.
     */
    public const SUPPORTED = ['ar', 'fr', 'en', 'ary'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = null;
        $supportedLanguages = self::SUPPORTED;

        // Priority 0: an explicit locale in the URL (/fr, /en, …). Public
        // marketing pages are served under a locale prefix so each language has
        // a real, indexable URL — a crawler has no session, so without this it
        // only ever sees the guest default (BAN-263).
        $routeLocale = $request->route('locale');
        if ($routeLocale && in_array($routeLocale, $supportedLanguages, true)) {
            $locale = $routeLocale;
        }

        // Per-client default for anonymous/guest visitors. Defaults to 'fr'
        // when a client doesn't set it, which since BAN-330 is also what
        // drivedesk sets -- the two agree today, so a test that only asserts
        // 'fr' cannot tell which one produced it.
        $clientDefault = config('client.public_default_locale', 'fr');

        // Priority 1: Get from authenticated user. Guarded on $locale so a
        // locale in the URL wins here.
        //
        // Note this is not the last word for signed-in visitors: the XSS route
        // middleware runs after this group and re-asserts Auth::user()->lang
        // app-wide. That is intended — the locale prefix exists so guests and
        // crawlers get an indexable URL per language, while a signed-in user
        // keeps the language they chose. See LocaleUrlTest.
        if (! $locale && Auth::check() && Auth::user()->lang) {
            $locale = Auth::user()->lang;
        }

        // Priority 2: Get from session
        if (!$locale) {
            $locale = session('locale');
        }

        // Priority 3: the client's public default (drivedesk → 'ary', else 'fr')
        if (!$locale) {
            $locale = $clientDefault;
        }

        // Validate locale - fall back to the client default, then 'fr'
        if (!in_array($locale, $supportedLanguages)) {
            $locale = in_array($clientDefault, $supportedLanguages) ? $clientDefault : 'fr';
        }

        // Set the application locale
        app()->setLocale($locale);

        // Ensure session has the current locale
        session(['locale' => $locale]);

        return $next($request);
    }
}
