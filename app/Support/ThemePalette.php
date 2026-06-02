<?php

namespace App\Support;

/**
 * Derives a full light+dark shadcn/Tailwind token map from a brand hex color.
 *
 * Pure class — no framework dependencies, fully unit-testable.
 *
 * Usage:
 *   $palette = ThemePalette::derive('#3B82F6');
 *   // $palette['light'] => ['--primary' => '217 91% 60%', ...]
 *   // $palette['dark']  => ['--primary' => '217 91% 70%', ...]
 */
class ThemePalette
{
    // Fixed semantic hues — NOT derived from brand (CLAUDE.md §6 / spec §6)
    // All semantic bg tokens are kept dark enough (lum ≤ 0.18) so white fg achieves WCAG AA ≥4.5:1.
    private const SEMANTIC_LIGHT = [
        '--destructive'            => '0 84.2% 45%',
        '--destructive-foreground' => '210 40% 98%',
        '--success'                => '142 76% 27%',
        '--success-foreground'     => '0 0% 100%',
        '--warning'                => '32 95% 30%',
        '--warning-foreground'     => '0 0% 100%',
        '--info'                   => '221 83% 35%',
        '--info-foreground'        => '0 0% 100%',
    ];

    private const SEMANTIC_DARK = [
        '--destructive'            => '0 62.8% 30.6%',
        '--destructive-foreground' => '210 40% 98%',
        '--success'                => '142 76% 20%',   // dark green — AA against white (7.5:1)
        '--success-foreground'     => '0 0% 100%',
        '--warning'                => '32 90% 30%',    // dark amber — AA against white
        '--warning-foreground'     => '0 0% 100%',
        '--info'                   => '221 83% 35%',   // dark blue — AA against white (8.4:1)
        '--info-foreground'        => '0 0% 100%',
    ];

    // Hue base for neutral ramp per surface temperature
    private const NEUTRAL_HUE = ['cool' => 210, 'neutral' => 240, 'warm' => 30];

    // Neutral saturation per temperature (subtle tint)
    private const NEUTRAL_SAT = ['cool' => 20, 'neutral' => 10, 'warm' => 15];

    /**
     * @param  string      $brandHex  e.g. '#3B82F6'
     * @param  string|null $accentHex optional accent hex; null → derived from brand
     * @param  string      $neutral   'cool'|'neutral'|'warm'
     * @return array{light: array<string,string>, dark: array<string,string>}
     */
    public static function derive(string $brandHex, ?string $accentHex = null, string $neutral = 'cool'): array
    {
        [$h, $s, $l] = self::hexToHsl($brandHex);

        return [
            'light' => self::lightTokens($h, $s, $l, $accentHex, $neutral),
            'dark'  => self::darkTokens($h, $s, $l, $accentHex, $neutral),
        ];
    }

    // -------------------------------------------------------------------------
    // Light token map
    // -------------------------------------------------------------------------

    private static function lightTokens(float $h, float $s, float $l, ?string $accentHex, string $neutral): array
    {
        // Primary: clamp into usable button band
        $pL = max(30.0, min(55.0, $l));
        $pS = max(40.0, $s);
        $primaryHsl = self::fmt($h, $pS, $pL);
        $primaryFg  = self::contrastFg($h, $pS, $pL);

        // Secondary: low-sat brand surface
        $secS = max(5.0, $s * 0.12);
        $secL = 96.1;
        $secondaryHsl = self::fmt($h, $secS, $secL);
        $secondaryFg  = self::contrastFg($h, $secS, $secL);

        // Neutral ramp (light)
        $nH = self::NEUTRAL_HUE[$neutral]  ?? 210;
        $nS = self::NEUTRAL_SAT[$neutral]  ?? 20;
        $bgHsl       = self::fmt($nH, 0, 100);
        $fgHsl       = self::fmt(222.2, 84.0, 4.9);
        $cardHsl     = $bgHsl;
        $cardFg      = $fgHsl;
        $popoverHsl  = $bgHsl;
        $popoverFg   = $fgHsl;
        $mutedHsl    = self::fmt($nH, $nS, 96.1);
        // Start at L=46 (close to shadcn default 46.9%) and nudge darker until AA passes
        $mutedFg     = self::nudgedMutedFg($nH, $nS, 96.1);
        $borderHsl   = self::fmt(214.3, 31.8, 91.4);
        $inputHsl    = $borderHsl;

        // Accent
        [$accentHsl, $accentFg] = self::resolveAccent($h, $s, $accentHex, false);

        // Ring = primary hue
        $ringHsl = $primaryHsl;

        $charts = self::chartTokens($h, $s, true);

        return array_merge([
            '--background'           => $bgHsl,
            '--foreground'           => $fgHsl,
            '--card'                 => $cardHsl,
            '--card-foreground'      => $cardFg,
            '--popover'              => $popoverHsl,
            '--popover-foreground'   => $popoverFg,
            '--primary'              => $primaryHsl,
            '--primary-foreground'   => $primaryFg,
            '--secondary'            => $secondaryHsl,
            '--secondary-foreground' => $secondaryFg,
            '--muted'                => $mutedHsl,
            '--muted-foreground'     => $mutedFg,
            '--accent'               => $accentHsl,
            '--accent-foreground'    => $accentFg,
            '--border'               => $borderHsl,
            '--input'                => $inputHsl,
            '--ring'                 => $ringHsl,
            '--chart-1'              => $charts[0],
            '--chart-2'              => $charts[1],
            '--chart-3'              => $charts[2],
            '--chart-4'              => $charts[3],
            '--chart-5'              => $charts[4],
        ], self::SEMANTIC_LIGHT);
    }

    // -------------------------------------------------------------------------
    // Dark token map
    // -------------------------------------------------------------------------

    private static function darkTokens(float $h, float $s, float $l, ?string $accentHex, string $neutral): array
    {
        // Primary dark: lighten + slightly desaturate
        $pL = max(60.0, min(80.0, $l + 20.0));
        $pS = max(40.0, $s * 0.9);
        $primaryHsl = self::fmt($h, $pS, $pL);
        $primaryFg  = self::contrastFg($h, $pS, $pL);

        // Secondary dark
        $secS = max(5.0, $s * 0.08);
        $secL = 17.5;
        $secondaryHsl = self::fmt($h, $secS, $secL);
        $secondaryFg  = '210 40% 98%';

        // Neutral ramp (dark)
        $nH = self::NEUTRAL_HUE[$neutral]  ?? 210;
        $bgHsl       = self::fmt($nH, 20.0, 5.0);
        $fgHsl       = '210 40% 98%';
        $cardHsl     = $bgHsl;
        $cardFg      = $fgHsl;
        $popoverHsl  = $bgHsl;
        $popoverFg   = $fgHsl;
        $mutedHsl    = self::fmt($nH, 20.0, 17.5);
        $mutedFg     = self::fmt($nH, 15.0, 65.1);
        $borderHsl   = self::fmt($nH, 20.0, 17.5);
        $inputHsl    = $borderHsl;

        // Accent dark
        [$accentHsl, $accentFg] = self::resolveAccent($h, $s, $accentHex, true);

        $ringHsl = $primaryHsl;

        $charts = self::chartTokens($h, $s, false);

        return array_merge([
            '--background'           => $bgHsl,
            '--foreground'           => $fgHsl,
            '--card'                 => $cardHsl,
            '--card-foreground'      => $cardFg,
            '--popover'              => $popoverHsl,
            '--popover-foreground'   => $popoverFg,
            '--primary'              => $primaryHsl,
            '--primary-foreground'   => $primaryFg,
            '--secondary'            => $secondaryHsl,
            '--secondary-foreground' => $secondaryFg,
            '--muted'                => $mutedHsl,
            '--muted-foreground'     => $mutedFg,
            '--accent'               => $accentHsl,
            '--accent-foreground'    => $accentFg,
            '--border'               => $borderHsl,
            '--input'                => $inputHsl,
            '--ring'                 => $ringHsl,
            '--chart-1'              => $charts[0],
            '--chart-2'              => $charts[1],
            '--chart-3'              => $charts[2],
            '--chart-4'              => $charts[3],
            '--chart-5'              => $charts[4],
        ], self::SEMANTIC_DARK);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Find the lightest muted-foreground L that still passes AA against the muted background. */
    private static function nudgedMutedFg(float $bgH, float $bgS, float $bgL): string
    {
        $bgLum = self::relLuminance($bgH, $bgS, $bgL);
        for ($l = 46.0; $l >= 30.0; $l -= 1.0) {
            if (self::contrastRatio($bgLum, self::relLuminance($bgH, 16.3, $l)) >= 4.5) {
                return self::fmt($bgH, 16.3, $l);
            }
        }
        return self::fmt($bgH, 16.3, 30.0);
    }

    private static function resolveAccent(float $brandH, float $brandS, ?string $accentHex, bool $dark): array
    {
        if ($accentHex) {
            [$aH, $aS, $aL] = self::hexToHsl($accentHex);
        } else {
            // Analogous hue +210° (complementary-ish)
            $aH = fmod($brandH + 210, 360);
            $aS = max(40.0, $brandS * 0.8);
            $aL = $dark ? 17.5 : 96.1;
        }

        if ($dark) {
            $aL = isset($accentHex) ? max(55.0, min(75.0, $aL + 20)) : 17.5;
        } else {
            $aL = isset($accentHex) ? max(30.0, min(60.0, $aL)) : 96.1;
        }

        $accentHsl = self::fmt($aH, $aS, $aL);
        $accentFg  = self::contrastFg($aH, $aS, $aL);

        return [$accentHsl, $accentFg];
    }

    private static function chartTokens(float $h, float $s, bool $light): array
    {
        $sat = max(60.0, $s);
        $lit = $light ? 55.0 : 55.0;
        $tokens = [];
        for ($i = 0; $i < 5; $i++) {
            $tokens[] = self::fmt(fmod($h + $i * 72, 360), $sat, $lit);
        }
        return $tokens;
    }

    /**
     * WCAG AA contrast gate: return a foreground that achieves ≥4.5:1 against the given bg.
     *
     * Uses luminance threshold to pick direction: dark bg (≤0.18) → light fg, else dark fg.
     * Nudges toward maximum contrast until the pair passes.
     */
    private static function contrastFg(float $h, float $s, float $l): string
    {
        $bgLum = self::relLuminance($h, $s, $l);

        if ($bgLum <= 0.18) {
            // Dark background → light foreground, nudge toward pure white
            for ($i = 0; $i <= 20; $i++) {
                $fl = min(100.0, 98.0 + $i);
                if (self::contrastRatio($bgLum, self::relLuminance(210, 40, $fl)) >= 4.5) {
                    return "210 40% {$fl}%";
                }
            }
            return '210 40% 98%';
        } else {
            // Light background → dark foreground, nudge toward pure black
            for ($i = 0; $i <= 20; $i++) {
                $fl = max(0.0, 11.2 - $i);
                if (self::contrastRatio($bgLum, self::relLuminance(222.2, 47.4, $fl)) >= 4.5) {
                    return "222.2 47.4% {$fl}%";
                }
            }
            return '222.2 47.4% 11.2%';
        }
    }

    private static function relLuminance(float $h, float $s, float $l): float
    {
        [$r, $g, $b] = self::hslToRgbFloat($h, $s / 100, $l / 100);
        $lin = fn(float $c) => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        return 0.2126 * $lin($r) + 0.7152 * $lin($g) + 0.0722 * $lin($b);
    }

    private static function contrastRatio(float $l1, float $l2): float
    {
        [$lighter, $darker] = $l1 >= $l2 ? [$l1, $l2] : [$l2, $l1];
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private static function hslToRgbFloat(float $h, float $s, float $l): array
    {
        if ($s === 0.0) return [$l, $l, $l];
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $h /= 360;
        return [
            self::hueChannel($p, $q, $h + 1 / 3),
            self::hueChannel($p, $q, $h),
            self::hueChannel($p, $q, $h - 1 / 3),
        ];
    }

    private static function hueChannel(float $p, float $q, float $t): float
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1 / 2) return $q;
        if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
        return $p;
    }

    /** Hex color → [H°, S%, L%] (1-decimal precision). */
    public static function hexToHsl(string $hex): array
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
            $h = $s = 0.0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            $h = match (true) {
                $max === $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $max === $g => ($b - $r) / $d + 2,
                default     => ($r - $g) / $d + 4,
            };
            $h /= 6;
        }

        return [round($h * 360, 1), round($s * 100, 1), round($l * 100, 1)];
    }

    private static function fmt(float $h, float $s, float $l): string
    {
        return round($h, 1) . ' ' . round($s, 1) . '% ' . round($l, 1) . '%';
    }
}
