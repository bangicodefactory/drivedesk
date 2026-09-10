<?php

/*
 * Feature-flag env overrides.
 *
 * Resolution order (highest precedence first):
 *   1. FEATURE_* env var (emergency override via .env or GitHub Environment)
 *   2. config/clients/<client>.php features array  (set by ClientServiceProvider)
 *   3. config/clients/_default.php features array
 *
 * A null value here means "no env override — fall through to client config".
 * Use feature('name') anywhere; never read this file directly.
 */
return [
    // 'paypal' and 'stripe' retired (BAN-336). Neither was read anywhere:
    // no feature() call, no feature: middleware, no JSX. They gated nothing,
    // and the credential forms they nominally belonged to were removed in
    // BAN-335 along with the last trace of either integration.
    'booking_payment'  => env('FEATURE_BOOKING_PAYMENT', null),
    'excel_import'     => env('FEATURE_EXCEL_IMPORT', null),
    'multi_branch'     => env('FEATURE_MULTI_BRANCH', null),
    'tva_renumber'     => env('FEATURE_TVA_RENUMBER', null),
    'signatures'       => env('FEATURE_SIGNATURES', null),
    'demo_gateway'     => env('FEATURE_DEMO_GATEWAY', null),
    'cash_split'       => env('FEATURE_CASH_SPLIT', null),
    'invoice_on_full_payment' => env('FEATURE_INVOICE_ON_FULL_PAYMENT', null),
    'traffic_violations' => env('FEATURE_TRAFFIC_VIOLATIONS', null),
    'public_storefront' => env('FEATURE_PUBLIC_STOREFRONT', null),
    'registration'     => env('FEATURE_REGISTRATION', null),
];
