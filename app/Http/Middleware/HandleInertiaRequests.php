<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    // Precomputed HSL values for each theme color (primary, primary-foreground)
    private const PRIMARY_MAP = [
        'color1' => ['203.7 75.7% 42.0%', '210 40% 98%'],
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
            'branding' => $this->buildBranding(),
        ];
    }

    private function buildBranding(): array
    {
        try {
            $s = settings();
        } catch (\Throwable) {
            $s = settingsKeys();
        }

        [$primary, $primaryFg] = $this->resolvePrimary($s);

        return [
            'cssVars' => [
                '--primary'            => $primary,
                '--primary-foreground' => $primaryFg,
                '--ring'               => $primary,
            ],
            'layoutMode'      => $s['layout_mode']      ?? 'lightmode',
            'layoutDirection' => $s['layout_direction'] ?? 'ltrmode',
        ];
    }

    private function resolvePrimary(array $s): array
    {
        if (($s['color_type'] ?? '') === 'own_color' && !empty($s['own_color_code'])) {
            [$h, $sat, $l] = $this->hexToHsl($s['own_color_code']);
            $fg = $l > 60 ? '222.2 47.4% 11.2%' : '210 40% 98%';
            return ["{$h} {$sat}% {$l}%", $fg];
        }

        return self::PRIMARY_MAP[$s['theme_color'] ?? 'color1']
            ?? self::PRIMARY_MAP['color1'];
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
