import { useState } from 'react';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { Button }   from '@/components/ui/button';
import { Input }    from '@/components/ui/input';
import { Label }    from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Switch }   from '@/components/ui/switch';
import { Badge }    from '@/components/ui/badge';
import { CheckCircle2, Paintbrush, Sun, Moon, Monitor } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

// ── JS mirror of ThemePalette (subset for live preview) ──────────────────────
// Kept in sync with App\Support\ThemePalette via the shared fixture test.

function hexToHsl(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.slice(0, 2), 16) / 255;
    const g = parseInt(hex.slice(2, 4), 16) / 255;
    const b = parseInt(hex.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h, s;
    const l = (max + min) / 2;
    if (max === min) {
        h = s = 0;
    } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            default: h = (r - g) / d + 4;
        }
        h /= 6;
    }
    return [Math.round(h * 360 * 10) / 10, Math.round(s * 100 * 10) / 10, Math.round(l * 100 * 10) / 10];
}

function hslFmt(h, s, l) {
    return `${Math.round(h * 10) / 10} ${Math.round(s * 10) / 10}% ${Math.round(l * 10) / 10}%`;
}

function relLuminance(h, s, l) {
    const f = (c) => c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    const hsl2rgb = (H, S, L) => {
        if (S === 0) return [L, L, L];
        const q = L < 0.5 ? L * (1 + S) : L + S - L * S;
        const p = 2 * L - q;
        const hue = (t) => {
            t = ((t % 1) + 1) % 1;
            if (t < 1/6) return p + (q - p) * 6 * t;
            if (t < 1/2) return q;
            if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        };
        return [hue(H/360 + 1/3), hue(H/360), hue(H/360 - 1/3)];
    };
    const [r, g, b] = hsl2rgb(h, s / 100, l / 100);
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
}

function contrastFg(h, s, l) {
    const bg = relLuminance(h, s, l);
    if (bg <= 0.18) return '210 40% 98%';
    for (let i = 0; i <= 20; i++) {
        const fl = Math.max(0, 11.2 - i);
        const fg = relLuminance(222.2, 47.4, fl);
        const cr = bg >= fg ? (bg + 0.05) / (fg + 0.05) : (fg + 0.05) / (bg + 0.05);
        if (cr >= 4.5) return `222.2 47.4% ${fl}%`;
    }
    return '222.2 47.4% 11.2%';
}

function nudgedMutedFg(bgH, bgS, bgL) {
    const bgLum = relLuminance(bgH, bgS, bgL);
    for (let l = 46; l >= 30; l--) {
        const fgLum = relLuminance(bgH, 16.3, l);
        const cr = bgLum >= fgLum ? (bgLum + 0.05) / (fgLum + 0.05) : (fgLum + 0.05) / (bgLum + 0.05);
        if (cr >= 4.5) return hslFmt(bgH, 16.3, l);
    }
    return hslFmt(bgH, 16.3, 30);
}

function derivePalette(brandHex) {
    if (!brandHex || !/^#[0-9A-Fa-f]{6}$/.test(brandHex)) return null;
    const [h, s, l] = hexToHsl(brandHex);
    const pL = Math.max(30, Math.min(55, l));
    const pS = Math.max(40, s);
    const primary = hslFmt(h, pS, pL);
    const primaryFg = contrastFg(h, pS, pL);
    return { primary, primaryFg };
}

// ── Brand preset swatches (carrying PRIMARY_MAP forward) ─────────────────────

const PRESETS = [
    { label: 'Ocean',    hex: '#2563EB' },
    { label: 'Violet',   hex: '#7C3AED' },
    { label: 'Sky',      hex: '#0EA5E9' },
    { label: 'Rose',     hex: '#E11D48' },
    { label: 'Emerald',  hex: '#059669' },
    { label: 'Slate',    hex: '#2C3444' },
    { label: 'Teal',     hex: '#0D9488' },
    { label: 'Navy',     hex: '#1E3A5F' },
];

// ── Zod schema ────────────────────────────────────────────────────────────────

const HEX_RE = /^#[0-9A-Fa-f]{6}$/;

const schema = z.object({
    brand_color:   z.string().regex(HEX_RE, 'Must be a valid hex color (e.g. #3B82F6)').or(z.literal('')).optional(),
    accent_color:  z.string().regex(HEX_RE, 'Must be a valid hex color').or(z.literal('')).optional(),
    brand_neutral: z.enum(['cool', 'neutral', 'warm']).optional(),
    layout_mode:   z.enum(['lightmode', 'darkmode', 'systemmode']).optional(),
});

// ── Component ─────────────────────────────────────────────────────────────────

function Branding({ settings }) {
    const brandColor  = settings?.brand_color  || '';
    const accentColor = settings?.accent_color || '';

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            brand_color:   brandColor,
            accent_color:  accentColor,
            brand_neutral: settings?.brand_neutral || 'cool',
            layout_mode:   settings?.layout_mode   || 'lightmode',
        },
    });
    const { register, setValue, watch, formState: { errors, isSubmitting } } = form;

    const watchedBrand   = watch('brand_color');
    const watchedNeutral = watch('brand_neutral');
    const watchedMode    = watch('layout_mode');

    const [autoAccent, setAutoAccent] = useState(!accentColor);

    const preview = derivePalette(watchedBrand || '#1A1D29');

    function handlePreset(hex) {
        setValue('brand_color', hex);
    }

    function handleReset() {
        setValue('brand_color', '');
        setValue('accent_color', '');
        setValue('brand_neutral', 'cool');
        setValue('layout_mode', 'lightmode');
        setAutoAccent(true);
    }

    const contrastOk = preview !== null;

    return (
        <div className="max-w-2xl space-y-6 p-6">
            <div>
                <h1 className="text-2xl font-semibold flex items-center gap-2">
                    <Paintbrush className="h-6 w-6 text-primary" />
                    Branding &amp; Theme
                </h1>
                <p className="text-sm text-muted-foreground mt-1">
                    Set your agency's brand color. The app derives a full contrast-safe
                    palette for light and dark mode automatically.
                </p>
            </div>

            <form onSubmit={submit('post', route('setting.branding'))} className="space-y-6">

                {/* ── Brand color ─────────────────────────────────────────── */}
                <Card>
                    <CardHeader><CardTitle>Brand color</CardTitle></CardHeader>
                    <CardContent className="space-y-4">

                        {/* Presets */}
                        <div>
                            <Label className="text-xs text-muted-foreground uppercase tracking-wide mb-2 block">
                                Presets
                            </Label>
                            <div className="flex flex-wrap gap-2">
                                {PRESETS.map(({ label, hex }) => (
                                    <button
                                        key={hex}
                                        type="button"
                                        title={label}
                                        onClick={() => handlePreset(hex)}
                                        className="h-8 w-8 rounded-full border-2 transition-all hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        style={{
                                            backgroundColor: hex,
                                            borderColor: watchedBrand === hex ? 'currentColor' : 'transparent',
                                        }}
                                        aria-label={label}
                                    />
                                ))}
                            </div>
                        </div>

                        {/* Color picker + hex input */}
                        <div className="flex items-center gap-3">
                            <input
                                type="color"
                                value={watchedBrand || '#1A1D29'}
                                onChange={(e) => setValue('brand_color', e.target.value)}
                                className="h-10 w-14 cursor-pointer rounded border border-input bg-transparent p-0.5"
                                aria-label="Brand color picker"
                            />
                            <div className="flex-1">
                                <Input
                                    {...register('brand_color')}
                                    placeholder="#3B82F6"
                                    className="font-mono"
                                />
                                {errors.brand_color && (
                                    <p className="mt-1 text-xs text-destructive">{errors.brand_color.message}</p>
                                )}
                            </div>
                            {contrastOk && (
                                <div className="flex items-center gap-1 text-xs text-success whitespace-nowrap">
                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                    AA ✓
                                </div>
                            )}
                        </div>

                        {/* Live preview */}
                        {watchedBrand && (
                            <div
                                className="rounded-lg border p-4 space-y-3"
                                style={preview ? {
                                    '--preview-primary': preview.primary,
                                    '--preview-primary-fg': preview.primaryFg,
                                } : {}}
                            >
                                <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">
                                    Live preview
                                </p>
                                <div className="flex flex-wrap gap-2 items-center">
                                    <button
                                        type="button"
                                        className="inline-flex items-center justify-center rounded-md px-3 py-1.5 text-sm font-medium"
                                        style={{ backgroundColor: `hsl(${preview?.primary})`, color: `hsl(${preview?.primaryFg})` }}
                                    >
                                        Save changes
                                    </button>
                                    <span
                                        className="text-sm font-medium cursor-pointer"
                                        style={{ color: `hsl(${preview?.primary})` }}
                                    >
                                        Learn more →
                                    </span>
                                    <input
                                        readOnly
                                        placeholder="Focused input"
                                        className="h-8 rounded border px-2 text-sm outline-none"
                                        style={{ borderColor: `hsl(${preview?.primary})`, boxShadow: `0 0 0 2px hsl(${preview?.primary} / 0.25)` }}
                                    />
                                    <Badge style={{ backgroundColor: `hsl(${preview?.primary})`, color: `hsl(${preview?.primaryFg})` }}>
                                        Active
                                    </Badge>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* ── Accent color ─────────────────────────────────────────── */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Accent color</CardTitle>
                            <div className="flex items-center gap-2 text-sm">
                                <Switch
                                    checked={autoAccent}
                                    onCheckedChange={(v) => {
                                        setAutoAccent(v);
                                        if (v) setValue('accent_color', '');
                                    }}
                                    id="auto-accent"
                                />
                                <Label htmlFor="auto-accent" className="cursor-pointer text-sm">Auto from brand</Label>
                            </div>
                        </div>
                    </CardHeader>
                    {!autoAccent && (
                        <CardContent>
                            <div className="flex items-center gap-3">
                                <input
                                    type="color"
                                    value={watch('accent_color') || '#10B981'}
                                    onChange={(e) => setValue('accent_color', e.target.value)}
                                    className="h-10 w-14 cursor-pointer rounded border border-input bg-transparent p-0.5"
                                    aria-label="Accent color picker"
                                />
                                <div className="flex-1">
                                    <Input
                                        {...register('accent_color')}
                                        placeholder="#10B981"
                                        className="font-mono"
                                    />
                                    {errors.accent_color && (
                                        <p className="mt-1 text-xs text-destructive">{errors.accent_color.message}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    )}
                </Card>

                {/* ── Surface temperature ──────────────────────────────────── */}
                <Card>
                    <CardHeader><CardTitle>Surface temperature</CardTitle></CardHeader>
                    <CardContent>
                        <p className="text-sm text-muted-foreground mb-3">
                            Tints backgrounds and borders toward the brand's color temperature.
                        </p>
                        <input type="hidden" {...register('brand_neutral')} />
                        <div className="flex gap-2">
                            {(['cool', 'neutral', 'warm']).map((t) => (
                                <button
                                    key={t}
                                    type="button"
                                    onClick={() => setValue('brand_neutral', t)}
                                    className={`flex-1 rounded-md border px-3 py-2 text-sm capitalize transition-colors ${
                                        watchedNeutral === t
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'hover:bg-accent hover:text-accent-foreground'
                                    }`}
                                >
                                    {t}
                                </button>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* ── Default mode ─────────────────────────────────────────── */}
                <Card>
                    <CardHeader><CardTitle>Default mode</CardTitle></CardHeader>
                    <CardContent>
                        <input type="hidden" {...register('layout_mode')} />
                        <div className="flex gap-2">
                            {[
                                { value: 'lightmode',  label: 'Light',  Icon: Sun },
                                { value: 'darkmode',   label: 'Dark',   Icon: Moon },
                                { value: 'systemmode', label: 'System', Icon: Monitor },
                            ].map(({ value, label, Icon }) => (
                                <button
                                    key={value}
                                    type="button"
                                    onClick={() => setValue('layout_mode', value)}
                                    className={`flex-1 flex items-center justify-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors ${
                                        watchedMode === value
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'hover:bg-accent hover:text-accent-foreground'
                                    }`}
                                >
                                    <Icon className="h-4 w-4" />
                                    {label}
                                </button>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* ── Actions ──────────────────────────────────────────────── */}
                <div className="flex items-center justify-between">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={handleReset}
                    >
                        Reset to default
                    </Button>
                    <Button type="submit" disabled={isSubmitting}>
                        {isSubmitting ? 'Saving…' : 'Save branding'}
                    </Button>
                </div>
            </form>
        </div>
    );
}

Branding.layout = (page) => (
    <AdminLayout breadcrumbs={[{ label: 'Settings' }, { label: 'Branding & Theme' }]}>
        {page}
    </AdminLayout>
);

export default Branding;
