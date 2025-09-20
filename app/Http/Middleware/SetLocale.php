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
        $supportedLanguages = ['ar', 'fr', 'en'];

        // Priority 1: Get from authenticated user
        if (Auth::check() && Auth::user()->lang) {
            $locale = Auth::user()->lang;
        }

        // Priority 2: Get from session
        if (!$locale) {
            $locale = session('locale');
        }

        // Priority 3: Default to 'fr'
        if (!$locale) {
            $locale = 'fr';
        }

        // Validate locale - fallback to 'fr' if invalid
        if (!in_array($locale, $supportedLanguages)) {
            $locale = 'fr';
        }

        // Set the application locale
        app()->setLocale($locale);

        // Ensure session has the current locale
        session(['locale' => $locale]);

        return $next($request);
    }
}
