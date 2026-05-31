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
