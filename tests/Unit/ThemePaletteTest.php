<?php

namespace Tests\Unit;

use App\Support\ThemePalette;
use PHPUnit\Framework\TestCase;

/**
 * BAN-243: ThemePalette derivation engine.
 *
 * Every (token, foreground) pair emitted by derive() must pass WCAG AA (≥4.5:1).
 * Fixed semantic tokens must be identical regardless of brand color.
 * No-brand-color path (legacy 3-var format) is tested in the feature layer.
 */
class ThemePaletteTest extends TestCase
{
    // ── Foreground pairs to check for WCAG AA ─────────────────────────────────

    private const FG_PAIRS = [
        ['--primary',     '--primary-foreground'],
        ['--secondary',   '--secondary-foreground'],
        ['--accent',      '--accent-foreground'],
        ['--muted',       '--muted-foreground'],
        ['--background',  '--foreground'],
        ['--card',        '--card-foreground'],
        ['--popover',     '--popover-foreground'],
        ['--destructive', '--destructive-foreground'],
        ['--success',     '--success-foreground'],
        ['--warning',     '--warning-foreground'],
        ['--info',        '--info-foreground'],
    ];

    private const FIXED_SEMANTIC_KEYS = [
        '--destructive', '--destructive-foreground',
        '--success',     '--success-foreground',
        '--warning',     '--warning-foreground',
        '--info',        '--info-foreground',
    ];

    // ── Matrix of brand hexes incl. pathological cases ────────────────────────

    public static function brandHexProvider(): array
    {
        return [
            'typical blue'    => ['#3B82F6'],
            'typical green'   => ['#10B981'],
            'typical red'     => ['#EF4444'],
            'near-black'      => ['#1A1D29'],
            'pure black'      => ['#000000'],
            'pure white'      => ['#FFFFFF'],
            'neon yellow'     => ['#FFFF00'],
            'neon cyan'       => ['#00FFFF'],
            'neon magenta'    => ['#FF00FF'],
            'dark brand'      => ['#0F172A'],
            'mid gray'        => ['#6B7280'],
            'orange'          => ['#F97316'],
            'purple'          => ['#8B5CF6'],
        ];
    }

    /** @dataProvider brandHexProvider */
    public function test_derive_returns_light_and_dark_maps(string $hex): void
    {
        $palette = ThemePalette::derive($hex);

        $this->assertArrayHasKey('light', $palette);
        $this->assertArrayHasKey('dark', $palette);
        $this->assertNotEmpty($palette['light']);
        $this->assertNotEmpty($palette['dark']);
    }

    /** @dataProvider brandHexProvider */
    public function test_light_tokens_have_all_required_keys(string $hex): void
    {
        $light = ThemePalette::derive($hex)['light'];

        $required = [
            '--background', '--foreground', '--card', '--card-foreground',
            '--popover', '--popover-foreground', '--primary', '--primary-foreground',
            '--secondary', '--secondary-foreground', '--muted', '--muted-foreground',
            '--accent', '--accent-foreground', '--border', '--input', '--ring',
            '--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5',
            '--destructive', '--destructive-foreground',
            '--success', '--success-foreground',
            '--warning', '--warning-foreground',
            '--info', '--info-foreground',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $light, "Missing {$key} in light map for {$hex}");
        }
    }

    /** @dataProvider brandHexProvider */
    public function test_dark_tokens_have_all_required_keys(string $hex): void
    {
        $dark = ThemePalette::derive($hex)['dark'];

        $required = [
            '--background', '--foreground', '--primary', '--primary-foreground',
            '--chart-1', '--success', '--warning', '--info',
        ];

        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $dark, "Missing {$key} in dark map for {$hex}");
        }
    }

    /** @dataProvider brandHexProvider */
    public function test_all_fg_pairs_pass_wcag_aa_in_light_mode(string $hex): void
    {
        $light = ThemePalette::derive($hex)['light'];

        foreach (self::FG_PAIRS as [$bgKey, $fgKey]) {
            if (!isset($light[$bgKey], $light[$fgKey])) continue;

            $cr = $this->contrastRatioFromHslStrings($light[$bgKey], $light[$fgKey]);
            $this->assertGreaterThanOrEqual(
                4.5,
                $cr,
                "Light mode: {$bgKey} vs {$fgKey} fails WCAG AA for brand {$hex} (ratio={$cr})"
            );
        }
    }

    /** @dataProvider brandHexProvider */
    public function test_all_fg_pairs_pass_wcag_aa_in_dark_mode(string $hex): void
    {
        $dark = ThemePalette::derive($hex)['dark'];

        foreach (self::FG_PAIRS as [$bgKey, $fgKey]) {
            if (!isset($dark[$bgKey], $dark[$fgKey])) continue;

            $cr = $this->contrastRatioFromHslStrings($dark[$bgKey], $dark[$fgKey]);
            $this->assertGreaterThanOrEqual(
                4.5,
                $cr,
                "Dark mode: {$bgKey} vs {$fgKey} fails WCAG AA for brand {$hex} (ratio={$cr})"
            );
        }
    }

    public function test_semantic_tokens_are_identical_across_brands(): void
    {
        $brands = ['#3B82F6', '#EF4444', '#10B981', '#000000', '#FFFFFF'];
        $reference = null;

        foreach ($brands as $hex) {
            $palette = ThemePalette::derive($hex);

            $lightSemantics = array_intersect_key($palette['light'], array_flip(self::FIXED_SEMANTIC_KEYS));
            $darkSemantics  = array_intersect_key($palette['dark'],  array_flip(self::FIXED_SEMANTIC_KEYS));

            if ($reference === null) {
                $reference = ['light' => $lightSemantics, 'dark' => $darkSemantics];
                continue;
            }

            $this->assertSame(
                $reference['light'],
                $lightSemantics,
                "Light semantic tokens differ for brand {$hex}"
            );
            $this->assertSame(
                $reference['dark'],
                $darkSemantics,
                "Dark semantic tokens differ for brand {$hex}"
            );
        }
    }

    public function test_hex_to_hsl_parses_correctly(): void
    {
        [$h, $s, $l] = ThemePalette::hexToHsl('#FF0000');
        $this->assertEqualsWithDelta(0, $h, 1);
        $this->assertEqualsWithDelta(100, $s, 1);
        $this->assertEqualsWithDelta(50, $l, 1);

        [$h, $s, $l] = ThemePalette::hexToHsl('#000000');
        $this->assertEqualsWithDelta(0, $h, 1);
        $this->assertEqualsWithDelta(0, $s, 1);
        $this->assertEqualsWithDelta(0, $l, 1);

        [$h, $s, $l] = ThemePalette::hexToHsl('#FFFFFF');
        $this->assertEqualsWithDelta(0, $h, 1);
        $this->assertEqualsWithDelta(0, $s, 1);
        $this->assertEqualsWithDelta(100, $l, 1);
    }

    public function test_shorthand_hex_is_expanded(): void
    {
        $full  = ThemePalette::hexToHsl('#336699');
        $short = ThemePalette::hexToHsl('#369');
        $this->assertSame($full, $short);
    }

    public function test_accent_hex_is_used_when_provided(): void
    {
        $withAccent    = ThemePalette::derive('#3B82F6', '#10B981')['light']['--accent'];
        $withoutAccent = ThemePalette::derive('#3B82F6')['light']['--accent'];
        $this->assertNotSame($withAccent, $withoutAccent);
    }

    public function test_neutral_temperature_affects_background(): void
    {
        $cool    = ThemePalette::derive('#3B82F6', null, 'cool')['light']['--background'];
        $warm    = ThemePalette::derive('#3B82F6', null, 'warm')['light']['--background'];
        $neutral = ThemePalette::derive('#3B82F6', null, 'neutral')['light']['--background'];

        // Backgrounds differ by temperature (different hue base)
        $this->assertNotSame($cool, $warm);
        $this->assertNotSame($cool, $neutral);
    }

    // ── WCAG math helpers ─────────────────────────────────────────────────────

    private function contrastRatioFromHslStrings(string $bg, string $fg): float
    {
        $l1 = $this->relLuminanceFromHslStr($bg);
        $l2 = $this->relLuminanceFromHslStr($fg);
        [$lighter, $darker] = $l1 >= $l2 ? [$l1, $l2] : [$l2, $l1];
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function relLuminanceFromHslStr(string $hsl): float
    {
        // Parse "H S% L%" format
        $hsl = str_replace('%', '', $hsl);
        [$h, $s, $l] = array_map('floatval', preg_split('/\s+/', trim($hsl)));
        return $this->relLuminance($h, $s / 100, $l / 100);
    }

    private function relLuminance(float $h, float $s, float $l): float
    {
        [$r, $g, $b] = $this->hslToRgb($h, $s, $l);
        $lin = fn(float $c) => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        return 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
    }

    private function hslToRgb(float $h, float $s, float $l): array
    {
        if ($s === 0.0) return [$l, $l, $l];
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $h /= 360;
        $hue = fn($p, $q, $t) => match (true) {
            ($t += ($t < 0 ? 1 : ($t > 1 ? -1 : 0))) && false => 0,
            $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
            $t < 1 / 2 => $q,
            $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
            default     => $p,
        };
        return [$hue($p, $q, $h + 1 / 3), $hue($p, $q, $h), $hue($p, $q, $h - 1 / 3)];
    }
}
