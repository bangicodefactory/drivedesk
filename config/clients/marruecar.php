<?php

return [

    'name'               => 'MarrueCar',
    'default_locale'     => 'fr',
    'supported_locales'  => ['fr', 'en', 'ar'],

    // Anonymous/guest visitors land on the storefront in French — MarrueCar's
    // primary customer-facing language. Read by App\Http\Middleware\SetLocale.
    'public_default_locale' => 'fr',

    /*
     * MarrueCar is a real single-agency tenant: a B2C car-rental storefront,
     * not the platform's own demo/sales surface. `public_storefront` stays on
     * (the default from _default.php); everything else defaults off unless the
     * business needs it.
     */
    'features' => [
        'paypal'          => false,
        'stripe'          => false,
        'subscriptions'   => false,
        'booking_payment' => false,
        'excel_import'    => true,
        'multi_branch'    => false,
        'tva_renumber'    => true,
        'signatures'      => true,
        'demo_gateway'    => false,
        'cash_split'      => false,
        'invoice_on_full_payment' => false,
        'traffic_violations' => false,
        // MarrueCar's public face IS a B2C rental storefront — explicit for
        // clarity even though `true` is already _default.php's value.
        'public_storefront' => true,
    ],

    /*
     * Public SEO copy (BAN-262), written in French to match the storefront's
     * default audience/locale. Description kept under ~155 chars.
     */
    'seo' => [
        'title'       => 'MarrueCar — Location de Voitures à Tétouan, Maroc',
        'description' => "Location de voitures fiable à Tétouan et Tanger : véhicules récents, prix transparents, prise en charge à l'agence ou à l'aéroport. Réservez en ligne.",
        'site_name'   => 'MarrueCar',
        'og_image'    => '/images/marruecar-og.png',
    ],

    /*
     * Explicit provider class — Str::studly('marruecar') yields 'Marruecar',
     * not 'MarrueCar'.
     */
    'provider_class' => \App\Clients\MarrueCar\Providers\MarrueCarServiceProvider::class,

    /*
     * Interface → concrete bindings. MarrueCar uses the platform defaults
     * (the services subclass Default* with no overrides yet); listed here so
     * ClientServiceProvider resolves them and future divergence has a home.
     */
    'bindings' => [
        \App\Contracts\PricingServiceContract::class
            => \App\Clients\MarrueCar\Services\MarrueCarPricingService::class,
        \App\Contracts\TvaServiceContract::class
            => \App\Clients\MarrueCar\Services\MarrueCarTvaService::class,
    ],

    'branding_seed' => [
        'app_name'        => 'MarrueCar',
        'brand_color'     => '#1B7AFC',
        'company_logo'    => 'logo.png',
        'meta_seo_title'  => 'MarrueCar — Location de Voitures, simplifiée',
        'company_name'    => 'MarrueCar',
        'company_email'   => 'marruecarsarl@gmail.com',
        'company_phone'   => '+212602-793425',
        'company_address' => 'Numero 16, Lot Mounia, Av Tizi Ouasli, Tétouan 93000, Morocco',
        // company_whatsapp, social_facebook/instagram and hours_* are added in
        // the follow-up PR that introduces the Header/Footer layout consuming
        // them (see docs/inertia-shared-props.md).
    ],

    'terms' => [
        'rental_agreement' => <<<'EOD'
MarrueCar — Contrat de Location\n
Article 1 : Objet\n
Les présentes conditions générales définissent les droits et obligations de la société de location (le « Loueur ») et de toute personne louant un véhicule (le « Locataire »).\n
Article 2 : Éligibilité\n
Le Locataire doit être âgé d'au moins 21 ans et titulaire d'un permis de conduire valide depuis au moins 2 ans. Une pièce d'identité valide et une copie du permis de conduire sont exigées à la signature.\n
Article 3 : Durée\n
La durée de la location est fixée dans le contrat. Toute prolongation doit être demandée et approuvée avant la fin de la période initiale.\n
Article 4 : Prix et paiement\n
Le prix est convenu avant la signature et payé avant la remise du véhicule, sauf accord contraire. Une caution peut être exigée et sera remboursée au retour, sous réserve de l'état du véhicule.\n
Article 5 : Utilisation du véhicule\n
Le Locataire s'engage à utiliser le véhicule de manière responsable et licite. Le véhicule ne peut être sous-loué ni utilisé à des fins illégales. Le Locataire doit signaler immédiatement tout accident, vol ou panne.\n
Article 6 : Assurance\n
Les véhicules sont couverts par une assurance tous risques avec franchise. Le Locataire est responsable de la franchise indiquée dans le contrat.\n
Article 7 : Restitution\n
Le véhicule doit être restitué à la date, à l'heure et au lieu convenus, dans le même état et avec le même niveau de carburant. Tout retard peut entraîner des frais supplémentaires.\n
Article 8 : Responsabilité\n
Le Locataire est entièrement responsable de toute infraction commise pendant la période de location.\n
Article 9 : Résiliation\n
Le Loueur peut résilier le contrat sans préavis en cas de manquement aux présentes conditions. Toute annulation par le Locataire doit être notifiée au moins 48 heures à l'avance.\n
Article 10 : Litiges\n
Les parties rechercheront une solution amiable ; à défaut, les tribunaux compétents seront saisis.\n
EOD,
    ],
];
