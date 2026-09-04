import { usePage } from '@inertiajs/react';

/**
 * Reads the current locale's resources/lang/<locale>.json strings, shared via
 * HandleInertiaRequests. Never hardcode SPA copy — always go through this.
 */
export function useTranslations() {
    const { translations } = usePage().props;
    return (key, fallback = key) => translations?.[key] ?? fallback;
}
