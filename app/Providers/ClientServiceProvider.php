<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ClientServiceProvider extends ServiceProvider
{
    private string $client;

    public function register(): void
    {
        $this->client = config('app.client', 'directonderweg');
        $default      = config('clients._default', []);
        $specific     = config("clients.{$this->client}", []);

        // Client keys win over defaults.
        $resolved = array_replace_recursive($default, $specific);
        config(['client' => $resolved]);

        // Bind client-specific implementations to core interfaces.
        foreach ($resolved['bindings'] ?? [] as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }

        // Register the active client's own ServiceProvider.
        // config/clients/<client>.php may supply an explicit 'provider_class' to
        // avoid Str::studly() producing the wrong casing (e.g. 'directonderweg'
        // → 'Directonderweg' instead of 'DirectOnderweg').
        // Read from $specific (not $resolved) — _default.php must never define
        // provider_class, and the key is inherently client-specific.
        $clientProvider = $specific['provider_class']
            ?? sprintf(
                'App\\Clients\\%s\\Providers\\%sServiceProvider',
                Str::studly($this->client),
                Str::studly($this->client)
            );
        if (class_exists($clientProvider)) {
            $this->app->register($clientProvider);
        }
    }

    public function boot(): void
    {
        // Add the client overlay path directly to the already-initialised view
        // finder (BAN-179). addLocation() prepends, so client views shadow core
        // views of the same name. Using boot() + addLocation() is guaranteed
        // correct: the view finder exists by the time boot() runs, unlike
        // mutating view.paths config in register() which depends on load order.
        $overlayViews = base_path("app/Clients/{$this->client}/resources/views");
        if (is_dir($overlayViews)) {
            $this->app->make('view.finder')->addLocation($overlayViews);
        }
    }
}
