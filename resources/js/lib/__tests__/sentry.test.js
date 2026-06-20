import { describe, it, expect, vi, beforeEach } from 'vitest';
import * as Sentry from '@sentry/react';
import { initSentry } from '@/lib/sentry';

// BAN-261: browser-side Sentry must initialize only when a DSN is configured,
// so local dev and any DSN-less build stay silent, and must forward the
// release/environment tags so frontend issues line up with backend ones.

vi.mock('@sentry/react', () => ({ init: vi.fn() }));

describe('initSentry', () => {
    beforeEach(() => vi.clearAllMocks());

    it('is a no-op when VITE_SENTRY_DSN is absent (silent local/dev)', () => {
        expect(initSentry({ MODE: 'development' })).toBe(false);
        expect(Sentry.init).not.toHaveBeenCalled();
    });

    it('is a no-op for an empty DSN string', () => {
        expect(initSentry({ VITE_SENTRY_DSN: '' })).toBe(false);
        expect(Sentry.init).not.toHaveBeenCalled();
    });

    it('initializes with dsn/environment/release when a DSN is set', () => {
        const ok = initSentry({
            VITE_SENTRY_DSN: 'https://pub@o1.ingest.sentry.io/42',
            VITE_SENTRY_ENVIRONMENT: 'production',
            VITE_SENTRY_RELEASE: 'abc123',
        });
        expect(ok).toBe(true);
        expect(Sentry.init).toHaveBeenCalledTimes(1);
        const cfg = Sentry.init.mock.calls[0][0];
        expect(cfg.dsn).toBe('https://pub@o1.ingest.sentry.io/42');
        expect(cfg.environment).toBe('production');
        expect(cfg.release).toBe('abc123');
        expect(cfg.sendDefaultPii).toBe(false);
    });

    it('defaults traces sampling off and environment to MODE', () => {
        initSentry({ VITE_SENTRY_DSN: 'https://x@o.ingest.sentry.io/1', MODE: 'staging' });
        const cfg = Sentry.init.mock.calls[0][0];
        expect(cfg.tracesSampleRate).toBe(0);
        expect(cfg.environment).toBe('staging');
    });
});
