/**
 * JSDoc type definitions for the Inertia shared-props contract.
 *
 * Every React page receives these props via usePage().props.
 * Server source: app/Http/Middleware/HandleInertiaRequests::share()
 * Full contract:  docs/inertia-shared-props.md
 *
 * Usage in a page component:
 *   import { usePage } from '@inertiajs/react';
 *   const { auth, branding, client, flash } = usePage().props;
 */

// ---------------------------------------------------------------------------
// Auth
// ---------------------------------------------------------------------------

/**
 * @typedef {Object} AuthUser
 * @property {number}      id
 * @property {string}      name
 * @property {string}      email
 * @property {string}      type           - 'admin' | 'company' | 'driver' | etc.
 * @property {string|null} lang           - User's preferred locale code (e.g. 'en')
 * @property {string|null} profile        - Avatar filename
 * @property {string|null} company_name
 */

/**
 * @typedef {Object} Auth
 * @property {AuthUser|null} user
 * @property {string[]}      permissions  - Spatie permission slugs e.g. ['manage-cars','view-bookings']
 */

// ---------------------------------------------------------------------------
// Branding
// ---------------------------------------------------------------------------

/**
 * @typedef {Object} BrandingCssVars
 * @property {string} --primary
 * @property {string} --primary-foreground
 * @property {string} --ring
 */

/**
 * @typedef {Object} Branding
 * @property {string}          appName
 * @property {string}          logoUrl          - Filename relative to storage/logos or public/assets
 * @property {string}          faviconUrl
 * @property {BrandingCssVars} cssVars          - Applied to :root via applyBranding() in app.jsx
 * @property {'lightmode'|'darkmode'}    layoutMode
 * @property {'ltrmode'|'rtlmode'}       layoutDirection
 */

// ---------------------------------------------------------------------------
// Client / feature flags
// ---------------------------------------------------------------------------

/**
 * @typedef {Object} ClientFeatures
 * @property {boolean} paypal
 * @property {boolean} stripe
 * @property {boolean} subscriptions
 * @property {boolean} booking_payment
 * @property {boolean} excel_import
 * @property {boolean} multi_branch
 * @property {boolean} tva_renumber
 * @property {boolean} signatures
 */

/**
 * @typedef {Object} Client
 * @property {string}         name               - APP_CLIENT value e.g. 'directonderweg'
 * @property {string}         default_locale     - e.g. 'en'
 * @property {string[]}       supported_locales  - e.g. ['ar','en','fr']
 * @property {ClientFeatures} features
 */

// ---------------------------------------------------------------------------
// Flash
// ---------------------------------------------------------------------------

/**
 * @typedef {Object} Flash
 * @property {string|null} success
 * @property {string|null} error
 */

// ---------------------------------------------------------------------------
// Full shared props
// ---------------------------------------------------------------------------

/**
 * @typedef {Object} SharedProps
 * @property {Auth}                      auth
 * @property {Branding}                  branding
 * @property {Client}                    client
 * @property {Record<string,string>}     translations  - Current locale key→value strings
 * @property {Flash}                     flash
 */

export {};
