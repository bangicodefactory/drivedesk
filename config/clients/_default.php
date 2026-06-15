<?php

return [

    /*
     * Feature-flag defaults. Every client inherits these; per-client
     * files override individual keys via array_replace_recursive().
     * Set to today's behavior so existing deploys are unchanged.
     */
    'features' => [
        'paypal'          => true,
        'stripe'          => true,
        'subscriptions'   => true,
        'booking_payment' => true,
        'excel_import'    => true,
        'multi_branch'    => false,
        'tva_renumber'    => true,
        'signatures'      => true,
        // Public marketing landing + "Book a demo" gateway at /. Off for normal
        // tenants (the app is internal-only); on for demo/showcase clients.
        'demo_gateway'    => false,
    ],

    /*
     * Interface → concrete bindings resolved by ClientServiceProvider.
     * Core code injects the interface; each client supplies the class.
     */
    'bindings' => [],

    /*
     * branding_seed is not defined here because it is always client-specific.
     * Every client config file (config/clients/<client>.php) must define it.
     */

    /*
     * Client-specific copy for rental agreement terms.
     * Each client must define terms.rental_agreement. Empty string = no default.
     */
    'terms' => [
        'rental_agreement' => '',
    ],
];
