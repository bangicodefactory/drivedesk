<?php

namespace App\Support;

/**
 * Derives a full light+dark shadcn/Tailwind token map from a brand hex color.
 *
 * Pure class — no framework dependencies, fully unit-testable.
 *
 * The brand-driven colors (primary light/dark and the chart series) are derived
 * in OKLCH so their lightness is perceptually uniform across hues — a yellow and
 * a blue requested at the same target lightness read as equally "heavy", which
 * HSL cannot guarantee. Output stays in shadcn's "H S% L%" format (consumed via
 * hsl(var(--token))). Neutrals, fixed semantics and accent keep their HSL
 * derivation. WCAG AA for every foreground pair is still enforced by contrastFg.
 *
 * Usage:
 *   $palette = ThemePalette::derive('#3B82F6');
 *   // $palette['light'] => ['--primary' => '217 91% 60%', ...]
 */
class ThemePalette
{
    // Fixed semantic hues — NOT derived from brand (CLAUDE.md §6 / spec §6)
    // All semantic bg tokens are kept dark enough (lum ≤ 0.18) so white fg achieves WCAG AA ≥4.5:1.
    private const SEMANTIC_LIGHT = [
        '--destructive'            => '0 75% 42%',   // #ba1a1a — Velocity Drive error color
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
        [$h, $s, $l]    = self::hexToHsl($brandHex);     // neutrals / accent hue / secondary tint
        [$bL, $bC, $bH] = self::hexToOklch($brandHex);   // brand color lightness (perceptual)

        return [
            'light' => self::lightTokens($h, $s, $l, $bL, $bC, $bH, $accentHex, $neutral),
            'dark'  => self::darkTokens($h, $s, $l, $bL, $bC, $bH, $accentHex, $neutral),
        ];
    }

    // -------------------------------------------------------------------------
    // Light token map
    // -------------------------------------------------------------------------

    private static function lightTokens(float $h, float $s, float $l, float $bL, float $bC, float $bH, ?string $accentHex, string $neutral): array
    {
        // Primary (OKLCH): perceptually-uniform lightness band, brand chroma + hue.
        $primaryHsl = self::oklchToHsl(self::clampF($bL, 0.45, 0.62), $bC, $bH);
        $primaryFg  = self::contrastFgFromStr($primaryHsl);

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

        $charts = self::chartTokens($bL, $bC, $bH, true);

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

    private static function darkTokens(float $h, float $s, float $l, float $bL, float $bC, float $bH, ?string $accentHex, string $neutral): array
    {
        // Primary dark (OKLCH): lighter band, slightly reduced chroma.
        $primaryHsl = self::oklchToHsl(self::clampF($bL + 0.18, 0.70, 0.86), $bC * 0.9, $bH);
        $primaryFg  = self::contrastFgFromStr($primaryHsl);

        // Secondary dark
        $secS = max(5.0, $s * 0.08);
        $secL = 17.5;
        $secondaryHsl = self::fmt($h, $secS, $secL);
        $secondaryFg  = '210 40% 98%';

        // Neutral ramp (dark) — use temperature-specific saturation like light mode
        $nH = self::NEUTRAL_HUE[$neutral] ?? 210;
        $nS = self::NEUTRAL_SAT[$neutral] ?? 20;
        $bgHsl       = self::fmt($nH, $nS, 5.0);
        $fgHsl       = '210 40% 98%';
        $cardHsl     = $bgHsl;
        $cardFg      = $fgHsl;
        $popoverHsl  = $bgHsl;
        $popoverFg   = $fgHsl;
        $mutedHsl    = self::fmt($nH, $nS, 17.5);
        $mutedFg     = self::fmt($nH, 15.0, 65.1);
        $borderHsl   = self::fmt($nH, $nS, 17.5);
        $inputHsl    = $borderHsl;

        // Accent dark
        [$accentHsl, $accentFg] = self::resolveAccent($h, $s, $accentHex, true);

        $ringHsl = $primaryHsl;

        $charts = self::chartTokens($bL, $bC, $bH, false);

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
    // Helpers — neutral / accent / contrast (HSL)
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
            $aL = $accentHex !== null ? max(55.0, min(75.0, $aL + 20)) : 17.5;
        } else {
            $aL = $accentHex !== null ? max(30.0, min(60.0, $aL)) : 96.1;
        }

        $accentHsl = self::fmt($aH, $aS, $aL);
        $accentFg  = self::contrastFg($aH, $aS, $aL);

        return [$accentHsl, $accentFg];
    }

    /** Five evenly-spaced chart colors at a perceptually-uniform lightness (OKLCH). */
    private static function chartTokens(float $bL, float $bC, float $bH, bool $light): array
    {
        $L = $light ? 0.62 : 0.72;          // perceptual lightness
        $C = max(0.10, min(0.16, $bC));     // keep charts colorful even for low-chroma brands
        $tokens = [];
        for ($i = 0; $i < 5; $i++) {
            $tokens[] = self::oklchToHsl($L, $C, fmod($bH + $i * 72, 360));
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

    /** contrastFg from an "H S% L%" string. */
    private static function contrastFgFromStr(string $hsl): string
    {
        [$h, $s, $l] = self::parseHslStr($hsl);
        return self::contrastFg($h, $s, $l);
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
        [$r, $g, $b] = self::hexToSrgb($hex);
        [$h, $s, $l] = self::rgbToHsl($r, $g, $b);
        return [$h, $s, $l];
    }

    private static function fmt(float $h, float $s, float $l): string
    {
        return round($h, 1) . ' ' . round($s, 1) . '% ' . round($l, 1) . '%';
    }

    private static function parseHslStr(string $hsl): array
    {
        $x = str_replace('%', '', $hsl);
        return array_map('floatval', preg_split('/\s+/', trim($x)));
    }

    private static function clampF(float $v, float $lo, float $hi): float
    {
        return max($lo, min($hi, $v));
    }

    // -------------------------------------------------------------------------
    // OKLCH ↔ sRGB (Björn Ottosson's OKLab)
    // -------------------------------------------------------------------------

    /** Hex → OKLCH [L (0-1), C, H°]. */
    public static function hexToOklch(string $hex): array
    {
        [$r, $g, $b] = self::hexToSrgb($hex);
        return self::srgbToOklch($r, $g, $b);
    }

    /** OKLCH → "H S% L%" HSL string, with chroma reduced until the color is in sRGB gamut. */
    private static function oklchToHsl(float $L, float $C, float $H): string
    {
        [$r, $g, $b] = self::oklchToSrgbClamped($L, $C, $H);
        [$h, $s, $l] = self::rgbToHsl($r, $g, $b);
        return self::fmt($h, $s, $l);
    }

    /** Reduce chroma stepwise until the (L,C,H) maps inside sRGB, then clamp channels. */
    private static function oklchToSrgbClamped(float $L, float $C, float $H): array
    {
        $C = max(0.0, $C);
        for ($i = 0; $i <= 40; $i++) {
            $c = $C * (1 - $i / 40);
            [$r, $g, $b] = self::oklchToSrgb($L, $c, $H);
            if (self::inGamut($r) && self::inGamut($g) && self::inGamut($b)) {
                return [self::clamp01($r), self::clamp01($g), self::clamp01($b)];
            }
        }
        [$r, $g, $b] = self::oklchToSrgb($L, 0.0, $H);
        return [self::clamp01($r), self::clamp01($g), self::clamp01($b)];
    }

    private static function oklchToSrgb(float $L, float $C, float $Hdeg): array
    {
        $h  = deg2rad($Hdeg);
        $a  = $C * cos($h);
        $bb = $C * sin($h);

        $l_ = $L + 0.3963377774 * $a + 0.2158037573 * $bb;
        $m_ = $L - 0.1055613458 * $a - 0.0638541728 * $bb;
        $s_ = $L - 0.0894841775 * $a - 1.2914855480 * $bb;

        $l = $l_ ** 3;
        $m = $m_ ** 3;
        $s = $s_ ** 3;

        $r = 4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
        $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
        $b = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

        return [self::linearToSrgb($r), self::linearToSrgb($g), self::linearToSrgb($b)];
    }

    private static function srgbToOklch(float $r, float $g, float $b): array
    {
        $lr = self::srgbToLinear($r);
        $lg = self::srgbToLinear($g);
        $lb = self::srgbToLinear($b);

        $l = 0.4122214708 * $lr + 0.5363325363 * $lg + 0.0514459929 * $lb;
        $m = 0.2119034982 * $lr + 0.6806995451 * $lg + 0.1073969566 * $lb;
        $s = 0.0883024619 * $lr + 0.2817188376 * $lg + 0.6299787005 * $lb;

        $l_ = self::cbrt($l);
        $m_ = self::cbrt($m);
        $s_ = self::cbrt($s);

        $L  = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a  = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $bb = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        $C = sqrt($a * $a + $bb * $bb);
        $H = rad2deg(atan2($bb, $a));
        if ($H < 0) $H += 360;

        return [$L, $C, $H];
    }

    private static function srgbToLinear(float $c): float
    {
        return $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    }

    private static function linearToSrgb(float $c): float
    {
        // Negative or tiny values pass through linearly (no pow of a negative → no NaN);
        // out-of-gamut results are caught by inGamut() and reduced.
        if ($c <= 0.0031308) return 12.92 * $c;
        return 1.055 * ($c ** (1 / 2.4)) - 0.055;
    }

    private static function cbrt(float $x): float
    {
        return $x < 0 ? -((-$x) ** (1 / 3)) : $x ** (1 / 3);
    }

    private static function inGamut(float $c): bool
    {
        return $c >= -0.001 && $c <= 1.001;
    }

    private static function clamp01(float $c): float
    {
        return max(0.0, min(1.0, $c));
    }

    private static function hexToSrgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];
    }

    /** sRGB (0-1) → [H°, S%, L%] (1-decimal precision). */
    private static function rgbToHsl(float $r, float $g, float $b): array
    {
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
}
