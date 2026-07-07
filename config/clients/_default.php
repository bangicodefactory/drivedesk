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
        // Split a cash payment over `cash_payment_max` into several receipts
        // each within the cap (Moroccan CGI art. 193 per-day cash ceiling),
        // instead of rejecting it. Off = today's behavior (reject).
        'cash_split'      => false,
        // Defer invoice (facture) creation until a booking is fully paid, then
        // emit one per payment. Off = today's behavior (one invoice per payment,
        // including partial payments).
        'invoice_on_full_payment' => false,
    ],

    /*
     * Legal ceiling (MAD) for a single cash payment/receipt. Above it, cash is
     * either rejected (cash_split off) or split into receipts each within this
     * cap (cash_split on). Read via config('client.cash_payment_max', 5000).
     */
    'cash_payment_max' => 5000,

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
