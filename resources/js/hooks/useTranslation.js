import { usePage } from '@inertiajs/react';

/**
 * App-wide translation helper.
 *
 * Reads the current-locale string map from the Inertia `translations` shared
 * prop (HandleInertiaRequests loads lang/<locale>.json). Returns a
 * `t(key, ...)` function mirroring Laravel's `__()`:
 *
 *   t('All Drivers')                          → looked-up string, key as fallback
 *   t('Missing', 'Fallback text')             → explicit fallback
 *   t('Page :current of :last', { current: 2, last: 3 })
 *                                             → Laravel-style `:name` placeholders
 *   t('key', 'fallback', { name: 'x' })       → both
 *
 * Placeholders with no matching replacement are left untouched; null/undefined
 * replacement values render empty (LengthAwarePaginator returns from/to = null
 * past the last page).
 */
export function useTranslation() {
    const { translations } = usePage().props;
    return (key, arg, replacements) => {
        let fallback = key;
        if (typeof arg === 'string') fallback = arg;
        else if (arg && typeof arg === 'object' && replacements === undefined) replacements = arg;

        let value = translations?.[key] ?? fallback;
        if (replacements) {
            value = value.replace(/:(\w+)/g, (match, name) =>
                name in replacements ? String(replacements[name] ?? '') : match
            );
        }
        return value;
    };
}
