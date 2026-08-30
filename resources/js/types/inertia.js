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
 * @property {string}          logoUrl          - Full URL (asset(Storage::url('upload/logo/...')))
 * @property {string}          faviconUrl       - Full URL
 * @property {BrandingCssVars} cssVars          - Applied to :root via applyBranding() in app.jsx
 * @property {'lightmode'|'darkmode'|'systemmode'} layoutMode
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
 * @property {boolean} cash_split
 * @property {boolean} traffic_violations
 * @property {boolean} public_storefront
 */

/**
 * @typedef {Object} Client
 * @property {string}         name               - APP_CLIENT value e.g. 'directonderweg'
 * @property {string}         default_locale     - e.g. 'en'
 * @property {string[]}       supported_locales  - e.g. ['ar','en','fr']
 * @property {ClientFeatures} features
 * @property {number}         cash_max           - legal cash ceiling (MAD), e.g. 5000
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
