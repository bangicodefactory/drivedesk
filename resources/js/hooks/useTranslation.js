import { usePage } from '@inertiajs/react';

/**
 * App-wide translation helper.
 *
 * Reads the current-locale string map from the Inertia `translations` shared
 * prop (HandleInertiaRequests loads lang/<locale>.json). Returns a `t(key,
 * fallback)` function: looks the key up, falls back to the key itself (or an
 * explicit fallback) when missing — so untranslated strings render readable
 * English rather than blank.
 *
 *   const t = useTranslation();
 *   <h1>{t('All Drivers')}</h1>
 */
export function useTranslation() {
    const { translations } = usePage().props;
    return (key, fallback = key) => translations?.[key] ?? fallback;
}
