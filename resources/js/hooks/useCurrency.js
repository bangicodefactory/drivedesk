import { usePage } from '@inertiajs/react';

/**
 * The tenant's currency symbol, and a formatter for a price with it.
 *
 * `CURRENCY_SYMBOL` is a per-tenant Setting (see settingsKeys() in
 * app/Helper/helper.php), shared as `branding.currencySymbol`. The storefront
 * prints a price on nearly every screen and used to hardcode "Dh" — which is
 * right for the current deployment and wrong for the next one, and is what
 * CLAUDE.md §10.2 rule 1 forbids.
 *
 * `price()` rounds to whole units, matching how the storefront has always
 * shown daily rates and totals; pass `decimals` where fractions matter.
 */
export function useCurrency() {
    const symbol = usePage().props.branding?.currencySymbol || 'Dh';

    return {
        symbol,
        price: (amount, decimals = 0) => `${Number(amount ?? 0).toFixed(decimals)} ${symbol}`,
    };
}
