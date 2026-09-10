import { usePage } from '@inertiajs/react';

/**
 * Whether this deployment offers card payment as a choice in the booking flow.
 *
 * Read the flag here and nowhere else. The path is `client.features.*`, not
 * `features.*`: HandleInertiaRequests nests the flags inside buildClient() and
 * there has never been a top-level `features` prop. The booking wizard read the
 * wrong one for a whole release (BAN-328) and its component test's mock had
 * invented the missing prop, so the test agreed with the bug.
 *
 * What it gates is the *offer*, not a payment. Nothing in this codebase can
 * charge a card -- no CMI package, no hosted-page redirect, no callback. With
 * this on, "card" is a stated intent that staff follow up on; with it off, cash
 * at the branch is the only option. Copy that promises one or the other has to
 * branch on this, which is the other reason it lives in one place: three
 * different screens were still telling visitors "nothing is paid online" after
 * the flag went on (BAN-334).
 */
export function useOnlinePayment() {
    return Boolean(usePage().props.client?.features?.booking_payment);
}
