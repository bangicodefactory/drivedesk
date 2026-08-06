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
     * 'ary' = Moroccan Arabic (Darija); a client opts into it via its
     * public_default_locale (e.g. drivedesk). Harmless for clients that
     * don't link to it. The base set is unchanged so directonderweg's
     * behaviour (nl users still fall back to fr, etc.) is preserved — note
     * that a client may list `nl` in supported_locales without it being
     * servable here, which is why App\Support\Locales intersects the two.
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
        // (today's behaviour) when a client doesn't set it — directonderweg
        // is unset, so it keeps defaulting to French.
        $clientDefault = config('client.public_default_locale', 'fr');

        // Priority 1: Get from authenticated user. Guarded on $locale so an
        // explicit locale in the URL still wins — without this the route prefix
        // would be silently ignored for any logged-in visitor.
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
