import * as Sentry from '@sentry/react';

/**
 * Browser-side Sentry initialization.
 *
 * No-op unless `VITE_SENTRY_DSN` is set, so local dev and any DSN-less build
 * stay silent. Backend errors are reported by sentry-laravel; this captures
 * what the server never sees — React render crashes, unhandled promise
 * rejections, and failed XHR/fetch in the SPA (e.g. the BAN-260 ReferenceError
 * that was invisible until someone noticed the dead button).
 *
 * The release/environment tags mirror the backend (`SENTRY_RELEASE` /
 * `SENTRY_ENVIRONMENT`) so a deploy's frontend and backend issues line up.
 *
 * @param {Record<string, any>} [env] injectable for tests; defaults to import.meta.env
 * @returns {boolean} whether Sentry was initialized
 */
export function initSentry(env = import.meta.env) {
    const dsn = env?.VITE_SENTRY_DSN;
    if (!dsn) return false;

    Sentry.init({
        dsn,
        environment: env.VITE_SENTRY_ENVIRONMENT || env.MODE || 'production',
        release: env.VITE_SENTRY_RELEASE || undefined,
        // Don't ship user PII to Sentry by default.
        sendDefaultPii: false,
        // Performance tracing off unless explicitly enabled per deploy.
        tracesSampleRate: env.VITE_SENTRY_TRACES_SAMPLE_RATE != null
            ? Number(env.VITE_SENTRY_TRACES_SAMPLE_RATE)
            : 0,
    });
    return true;
}
