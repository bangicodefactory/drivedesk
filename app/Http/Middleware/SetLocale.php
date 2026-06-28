<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = null;
        // 'ary' = Moroccan Arabic (Darija); a client opts into it via its
        // public_default_locale (e.g. drivedesk). Harmless for clients that
        // don't link to it. The base set is unchanged so directonderweg's
        // behaviour (nl users still fall back to fr, etc.) is preserved.
        $supportedLanguages = ['ar', 'fr', 'en', 'ary'];

        // Per-client default for anonymous/guest visitors. Defaults to 'fr'
        // (today's behaviour) when a client doesn't set it — directonderweg
        // is unset, so it keeps defaulting to French.
        $clientDefault = config('client.public_default_locale', 'fr');

        // Priority 1: Get from authenticated user
        if (Auth::check() && Auth::user()->lang) {
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
