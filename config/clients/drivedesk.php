<?php

return [

    'name'               => 'DriveDesk',
    'default_locale'     => 'en',
    'supported_locales'  => ['en', 'fr', 'nl', 'ar', 'ary'],

    // Anonymous/guest visitors (e.g. the marketing landing) default to Moroccan
    // Arabic (Darija, 'ary'). Logged-in users keep their own saved language.
    // Read by App\Http\Middleware\SetLocale; unset for other clients → 'fr'.
    'public_default_locale' => 'ary',

    // Where the public "Book a demo" form is delivered (DemoRequestController).
    'demo_request_to' => 'admin@bangicode.ma',

    /*
     * DriveDesk is the product's own reference/demo client — the base tenant
     * used to showcase the platform (and to host the marketing site that
     * promotes it to other rental agencies). All shippable features are on so
     * the demo shows the full product surface.
     */
    'features' => [
        'paypal'          => true,
        'stripe'          => true,
        'subscriptions'   => true,
        'booking_payment' => true,
        'excel_import'    => true,
        'multi_branch'    => true,
        'tva_renumber'    => true,
        'signatures'      => true,
        'demo_gateway'    => true,   // DriveDesk is the product's demo gateway
        'cash_split'      => true,   // split cash over the ceiling into compliant receipts
        'invoice_on_full_payment' => true,  // emit invoices only once a booking is fully paid
        'traffic_violations' => true,  // BAN-260: part of the full demo surface
        // DriveDesk sells the platform to rental agencies; its public face is
        // the B2B demo gateway at /, not a B2C rental storefront. The storefront
        // pages targeted the opposite audience (and /landing shipped seeded demo
        // fleet + invented testimonials), so they are off here. BAN-261.
        'public_storefront' => false,
    ],

    /*
     * Public SEO copy (BAN-262). Written in English rather than the guest
     * default locale (`ary`): the buyer here is a rental-agency owner, the
     * product is sold beyond Morocco, and a crawler is served the guest locale
     * regardless of who is searching. Description is 149 chars.
     */
    'seo' => [
        'title'       => 'DriveDesk — Car Rental Management Software',
        'description' => 'Run your car rental agency from one place: fleet, bookings, contracts, e-signature, invoicing and planning. Multilingual, white-label. Book a demo.',
        'site_name'   => 'DriveDesk',
        'og_image'    => '/images/drivedesk-og.png',
        // Prose for llms.txt. With SSR off, an assistant that does not execute
        // JavaScript sees an empty document, so this is the only description of
        // the product it can read.
        'llms_summary' => 'DriveDesk is car rental management software for rental agencies. '
            .'It covers fleet management, bookings, rental contracts with in-app e-signature, '
            .'invoicing and VAT, expenses, and a visual planning board. It is multilingual and '
            .'white-label: each agency runs it on its own domain and branding.',
    ],

    /*
     * Explicit provider class — Str::studly('drivedesk') yields 'Drivedesk',
     * not 'DriveDesk'.
     */
    'provider_class' => \App\Clients\DriveDesk\Providers\DriveDeskServiceProvider::class,

    /*
     * Interface → concrete bindings. DriveDesk uses the platform defaults
     * (the services subclass Default* with no overrides yet); listed here so
     * ClientServiceProvider resolves them and future divergence has a home.
     */
    'bindings' => [
        \App\Contracts\PricingServiceContract::class
            => \App\Clients\DriveDesk\Services\DriveDeskPricingService::class,
        \App\Contracts\TvaServiceContract::class
            => \App\Clients\DriveDesk\Services\DriveDeskTvaService::class,
    ],

    'branding_seed' => [
        'app_name'       => 'DriveDesk',
        'theme_color'    => 'color1',          // brand orange (#E5601E)
        'company_logo'   => 'logo.png',
        'meta_seo_title' => 'DriveDesk — Car Rental Management, simplified',
    ],

    'terms' => [
        'rental_agreement' => <<<'EOD'
DriveDesk — Rental Agreement\n
Article 1: Purpose\n
These general terms define the rights and obligations of the rental company (the "Lessor") and any person renting a vehicle (the "Renter").\n
Article 2: Eligibility\n
The Renter must be at least 21 years old and hold a driving licence valid for at least 2 years. A valid ID and a copy of the driving licence are required at signing.\n
Article 3: Duration\n
The rental period is set in the contract. Any extension must be requested and approved before the end of the initial term.\n
Article 4: Price & payment\n
The price is agreed before signing and paid before the vehicle is handed over, unless agreed otherwise. A deposit may be required and is refunded on return, subject to the vehicle's condition.\n
Article 5: Use of the vehicle\n
The Renter agrees to use the vehicle responsibly and lawfully. The vehicle may not be sub-let or used for illegal purposes. The Renter must immediately report any accident, theft or breakdown.\n
Article 6: Insurance\n
Vehicles are covered by comprehensive insurance with an excess. The Renter is liable for the excess shown in the contract.\n
Article 7: Return\n
The vehicle must be returned at the agreed date, time and place, in the same condition and with the same fuel level. Late returns may incur additional charges.\n
Article 8: Liability\n
The Renter is fully liable for any offences committed during the rental period.\n
Article 9: Termination\n
The Lessor may terminate the contract without notice for any breach of these terms. Cancellation by the Renter must be notified at least 48 hours in advance.\n
Article 10: Disputes\n
The parties will seek an amicable solution; failing that, the competent courts shall apply.\n
EOD,
    ],
];
