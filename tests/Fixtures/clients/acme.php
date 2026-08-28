<?php

/*
 * Test-only tenant. Loaded by Tests\Concerns\WithClient::asClient('acme') when
 * no config/clients/acme.php exists.
 *
 * A plain rental agency with the storefront on and every optional module off:
 * no online payments, no demo gateway, no cash splitting, no traffic
 * violations. Route-level suites use it as their neutral baseline so their
 * meaning does not change when a real client's config does (CLAUDE.md §10.2
 * rule 6) — a suite that needs a flag forces it with config([...]).
 *
 * The flag set mirrors what the suites were originally calibrated against
 * (the directonderweg client before the repo split); keep it stable.
 */
return [

    'name'              => 'Acme Rentals',
    'default_locale'    => 'nl',
    'supported_locales' => ['nl', 'fr', 'en', 'ar'],

    'features' => [
        'paypal'                  => false,
        'stripe'                  => false,
        'subscriptions'           => false,
        'booking_payment'         => false,
        'excel_import'            => true,
        'multi_branch'            => false,
        'tva_renumber'            => true,
        'signatures'              => true,
        'demo_gateway'            => false,
        'cash_split'              => false,
        'invoice_on_full_payment' => false,
        'traffic_violations'      => false,
        'public_storefront'       => true,
    ],

    'bindings' => [
        \App\Contracts\PricingServiceContract::class => \App\Services\DefaultPricingService::class,
        \App\Contracts\TvaServiceContract::class     => \App\Services\DefaultTvaService::class,
    ],

    'branding_seed' => [
        'app_name'       => 'Acme Rentals',
        'theme_color'    => 'color1',
        'company_logo'   => 'logo.png',
        'meta_seo_title' => 'Acme Rentals — Car Rental',
    ],
];
