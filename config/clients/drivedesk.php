<?php

return [

    'name'               => 'DriveDesk',
    'default_locale'     => 'en',
    // BAN-311: env paths. These are per-*customer* values living in a
    // per-*variant* file, and neither client config contained a single env()
    // call, so a customer that differed here needed a committed config of its
    // own. Defaults reproduce today's values exactly.
    // `env()` returns '' for a key that is present but empty, not the default,
    // and an empty list makes Locales::routeConstraint() emit '(?!)' -- every
    // locale-prefixed public URL 404s and hreflang disappears, silently. So the
    // default applies to a blank or comma-only value too.
    'supported_locales'  => array_values(array_filter(array_map(
        'trim',
        explode(',', trim((string) env('CLIENT_SUPPORTED_LOCALES', '')) !== ''
            ? (string) env('CLIENT_SUPPORTED_LOCALES')
            : 'fr,ar,en')
    ))) ?: ['fr', 'ar', 'en'],

    // Anonymous/guest visitors (e.g. the marketing landing) default to French.
    // Logged-in users keep their own saved language. Read by
    // App\Http\Middleware\SetLocale; unset for other clients → 'fr'.
    //
    // Was 'ary' (Moroccan Darija). Two reasons it is not: the copy filed under
    // ary.json was rewritten into Modern Standard Arabic, so 'ary' and 'ar'
    // were the same language at two names -- Locales::forPublicUrls() already
    // excluded it from public URLs for exactly that reason -- and ary.json
    // carries 63 keys against en.json's 1000+, so a guest landing on the
    // storefront got an RTL layout wrapped around mostly-French fallback copy.
    //
    // SetLocale::SUPPORTED still lists 'ary', deliberately: an account that
    // already chose it keeps it, and /language/ary still works. What changes is
    // only what an anonymous visitor gets by default.
    'public_default_locale' => trim((string) env('CLIENT_PUBLIC_DEFAULT_LOCALE', '')) ?: 'fr',

    // Where the public "Book a demo" form is delivered (DemoRequestController).
    'demo_request_to' => env('CLIENT_DEMO_REQUEST_TO', 'admin@bangicode.ma'),

    /*
     * DriveDesk is the product's own reference/demo client — the base tenant
     * used to showcase the platform (and to host the marketing site that
     * promotes it to other rental agencies). All shippable features are on so
     * the demo shows the full product surface.
     */
    'features' => [
        'paypal'          => true,
        'stripe'          => true,
        // On, deliberately and with a caveat (BAN-334).
        //
        // What this actually switches on is one tile in the /reserve wizard's
        // payment step. It gates no route, and nothing in this codebase can
        // charge a card: there is no CMI package in composer.json, no hosted-
        // page redirect, no callback controller, no webhook. What the visitor
        // picks is recorded as `booking_requests.payment_preference` -- a
        // stated intent, so staff know to ring them about card payment rather
        // than expecting cash at the desk.
        //
        // The copy on that tile has to keep saying so. RequestBookingController
        // validates payment_preference as in:cash,cmi and nothing downstream
        // treats 'cmi' as paid, so no money flow depends on this being right --
        // but a visitor's expectation does.
        //
        // The thing to watch: with this true, the day someone registers a route
        // behind feature:booking_payment it is live in production the moment it
        // merges. RouteIntegrityTest holds that line until a real callback
        // exists.
        'booking_payment' => true,
        'excel_import'    => true,
        'multi_branch'    => true,
        'tva_renumber'    => true,
        'signatures'      => true,
        'demo_gateway'    => true,   // DriveDesk is the product's demo gateway
        'cash_split'      => true,   // split cash over the ceiling into compliant receipts
        'invoice_on_full_payment' => true,  // emit invoices only once a booking is fully paid
        'traffic_violations' => true,  // BAN-260: part of the full demo surface
        // On since BAN-329. DriveDesk sells the platform to rental agencies and
        // its public face is still the B2B demo gateway at / -- that is
        // unchanged, `/` renders DemoGateway and HomeController does not let the
        // storefront claim it.
        //
        // What this opens, in full: /landing, /contact, /search,
        // /newsletter/subscribe -- and /reserve plus its signed confirmation,
        // which is the unauthenticated B2C booking wizard. That last pair is
        // easy to miss and is the larger surface: its sibling
        // POST /booking_request writes booking_requests rows carrying a guest's
        // name, email and phone. On this client those land against the demo
        // tenant.
        //
        // BAN-261 turned this off because those pages targeted the opposite
        // audience and shipped seeded demo fleet data plus invented
        // testimonials on a live commercial domain. The seeded fleet is the
        // point of a demo tenant, but the fabricated social proof is not:
        // /landing still renders four made-up testimonials and a hardcoded
        // five-star "2 Reviews" badge on every vehicle. Removing those is
        // tracked separately and should land before anyone treats this page as
        // a shop window.
        'public_storefront' => true,
        // Off even here. Nothing links to /register (the demo funnel runs
        // through DemoRequestController, which creates a manager under the
        // existing owner), so the route was reachable by URL alone. BAN-307.
        'registration' => false,
    ],

    /*
     * Public SEO copy (BAN-262). Written in English rather than the guest
     * default locale (French since BAN-330): the buyer here is a rental-agency owner, the
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
