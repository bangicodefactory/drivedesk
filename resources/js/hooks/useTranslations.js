import { usePage } from '@inertiajs/react';

/**
 * Returns a t(key, fallback) helper that looks up the current-locale
 * translation from Inertia shared props. Falls back to the key itself
 * when no translation is found.
 */
export function useTranslations() {
    const { translations } = usePage().props;
    return (key, fallback = key) => translations?.[key] ?? fallback;
}
