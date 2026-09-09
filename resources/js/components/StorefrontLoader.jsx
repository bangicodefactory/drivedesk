import { Loader2 } from 'lucide-react';

/**
 * Full-screen loading indicator shown while an Inertia visit is in flight
 * (wired up in StorefrontLayout via router.on('start'/'finish')). The public
 * storefront has no client-side data cache, so navigating between Landing →
 * Fleet card → Booking → Confirmation always re-fetches from the server —
 * this gives visible feedback instead of a page that looks frozen.
 *
 * Spins on a slow, deliberate 2s cycle (vs. the ~0.6-1s a busy default
 * spinner uses) to match the calm, minimal storefront aesthetic.
 */
export default function StorefrontLoader() {
    return (
        <div
            className="fixed inset-0 z-[60] flex items-center justify-center bg-background/70 backdrop-blur-sm"
            role="status"
            aria-live="polite"
            aria-label="Loading"
        >
            <Loader2 className="h-10 w-10 text-primary animate-spin motion-reduce:animate-none" style={{ animationDuration: '2s' }} />
        </div>
    );
}
