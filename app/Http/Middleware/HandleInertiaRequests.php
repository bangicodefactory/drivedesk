<?php

namespace App\Http\Middleware;

use App\Support\ThemePalette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    private ?array $cachedSettings = null;

    // Precomputed HSL values for each theme color (primary, primary-foreground)
    private const PRIMARY_MAP = [
        'color1' => ['221.2 83.2% 53.3%', '0 0% 100%'], // Aether Mobility electric blue #2563eb (default)
        'color2' => ['262.8 89.7% 50.6%', '210 40% 98%'],
        'color3' => ['201.7 100.0% 50.2%', '210 40% 98%'],
        'color4' => ['354.3 70.5% 53.5%', '210 40% 98%'],
        'color5' => ['162.2 72.5% 45.7%', '210 40% 98%'],
        'color6' => ['9.2 24.1% 21.2%',   '210 40% 98%'],
        'color7' => ['169.8 96.7% 23.9%', '210 40% 98%'],
        'color8' => ['205.3 49.6% 22.5%', '210 40% 98%'],
        'color9' => ['146.3 93.4% 23.9%', '210 40% 98%'],
    ];

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth'         => $this->buildAuth($request),
            'branding'     => $this->buildBranding(),
            'client'       => $this->buildClient(),
            'recaptcha'    => $this->buildRecaptcha(),
            'locale'       => app()->getLocale(),
            'translations' => $this->loadTranslations(),
            'flash'        => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    private function buildAuth(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return ['user' => null, 'permissions' => []];
        }

        return [
            'user' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'type'         => $user->type,
                'lang'         => $user->lang,
                'profile'      => $user->profile,
                'company_name' => $user->company_name,
            ],
            'permissions' => $user->getAllPermissions()
                ->pluck('name')
                ->values()
                ->toArray(),
        ];
    }

    // -------------------------------------------------------------------------
    // Branding (extends BAN-54 with logo + app name)
    // -------------------------------------------------------------------------

    private function buildBranding(): array
    {
        $s = $this->loadSettings();

        // BAN-243: if brand_color is set, derive the full light+dark palette.
        // Otherwise fall back to the legacy 3-var format (back-compat, spec §8).
        $brandHex = $s['brand_color'] ?? null;

        if ($brandHex) {
            $accentHex = $s['accent_color'] ?? null;
            $neutral   = $s['brand_neutral'] ?? 'cool';
            $cssVars   = ThemePalette::derive($brandHex, $accentHex ?: null, $neutral);
        } else {
            [$primary, $primaryFg] = $this->resolvePrimary($s);
            $cssVars = [
                '--primary'            => $primary,
                '--primary-foreground' => $primaryFg,
                '--ring'               => $primary,
            ];
        }

        return [
            'appName'    => $s['app_name'] ?? config('app.name', 'RentCar'),
            'logoUrl'    => asset(Storage::url('upload/logo/' . ($s['company_logo']    ?? 'logo.png'))),
            'faviconUrl' => asset(Storage::url('upload/logo/' . ($s['company_favicon'] ?? 'favicon.png'))),
            'cssVars'         => $cssVars,
            'layoutMode'      => $s['layout_mode']      ?? 'lightmode',
            'layoutDirection' => $s['layout_direction'] ?? 'ltrmode',
        ];
    }

    // -------------------------------------------------------------------------
    // Client / feature flags
    // -------------------------------------------------------------------------

    private function buildClient(): array
    {
        return [
            'name'              => config('app.client', 'directonderweg'),
            'default_locale'    => config('client.default_locale', config('app.locale', 'en')),
            'supported_locales' => config('client.supported_locales', []),
            'features'          => config('client.features', []),
        ];
    }

    // -------------------------------------------------------------------------
    // reCAPTCHA (BAN-204)
    // -------------------------------------------------------------------------

    private function buildRecaptcha(): array
    {
        $s = $this->loadSettings();

        return [
            'enabled' => ($s['google_recaptcha'] ?? 'off') === 'on',
            'siteKey' => $s['recaptcha_key'] ?? '',
        ];
    }

    private function loadSettings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }
        try {
            $this->cachedSettings = settings();
        } catch (\Throwable) {
            $this->cachedSettings = [];
        }
        return $this->cachedSettings;
    }

    // -------------------------------------------------------------------------
    // Translations
    // -------------------------------------------------------------------------

    private function loadTranslations(): object
    {
        $locale   = app()->getLocale();
        $jsonPath = resource_path("lang/{$locale}.json");

        if (file_exists($jsonPath)) {
            $decoded = json_decode(file_get_contents($jsonPath), true);

            return (object) ($decoded ?? []);
        }

        return new \stdClass();
    }

    // -------------------------------------------------------------------------
    // Theme helpers (BAN-54, unchanged)
    // -------------------------------------------------------------------------

    private function resolvePrimary(array $s): array
    {
        if (($s['color_type'] ?? '') === 'own_color' && !empty($s['own_color_code'])) {
            [$h, $sat, $l] = $this->hexToHsl($s['own_color_code']);
            $fg = $l > 60 ? '222.2 47.4% 11.2%' : '210 40% 98%';
            return ["{$h} {$sat}% {$l}%", $fg];
        }

        // If theme_color is explicitly set, honour it (preserves existing agency choices).
        // When no theme_color is set at all, use the Aether Mobility default (#2563eb).
        if (!empty($s['theme_color'])) {
            return self::PRIMARY_MAP[$s['theme_color']] ?? self::PRIMARY_MAP['color1'];
        }

        return ['221.2 83.2% 53.3%', '0 0% 100%']; // Aether Mobility primary #2563eb
    }

    private function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l   = ($max + $min) / 2;

        if ($max === $min) {
            $h = $sat = 0.0;
        } else {
            $d   = $max - $min;
            $sat = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            $h = match ($max) {
                $r      => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $g      => ($b - $r) / $d + 2,
                default => ($r - $g) / $d + 4,
            };
            $h /= 6;
        }

        return [round($h * 360, 1), round($sat * 100, 1), round($l * 100, 1)];
    }
}
