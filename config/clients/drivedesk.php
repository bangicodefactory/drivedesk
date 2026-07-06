<?php

return [

    'name'               => 'DriveDesk',
    'default_locale'     => 'en',
    'supported_locales'  => ['en', 'fr', 'nl', 'ar'],

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
