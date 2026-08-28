<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ClientServiceProvider extends ServiceProvider
{
    private string $client;
    private string $clientDir;

    public function register(): void
    {
        $this->client = config('app.client', 'drivedesk');
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
        // avoid Str::studly() producing the wrong casing (e.g. 'drivedesk'
        // → 'Drivedesk' instead of 'DriveDesk').
        // Read from $specific (not $resolved) — _default.php must never define
        // provider_class, and the key is inherently client-specific.
        $clientProvider = $specific['provider_class']
            ?? sprintf(
                'App\\Clients\\%s\\Providers\\%sServiceProvider',
                Str::studly($this->client),
                Str::studly($this->client)
            );

        // The directory under app/Clients/ matches the namespace segment of the
        // client's provider class (App\Clients\<Dir>\...). Derive it from there so
        // the view overlay path and the provider share one source of truth — the
        // raw slug ('drivedesk') and even Str::studly() ('Drivedesk')
        // produce the wrong casing for a StudlyCase dir like 'DriveDesk', which silently no-ops the
        // overlay on case-sensitive filesystems (BAN-179 regression → Sentry
        // DIRECTONDERWEG-3). Only trust the segment when the class actually lives
        // under App\Clients\<Dir>; a provider class registered elsewhere would
        // otherwise yield a garbage dir, so fall back to the studly slug.
        $segments        = explode('\\', ltrim($clientProvider, '\\'));
        $this->clientDir = (($segments[0] ?? null) === 'App' && ($segments[1] ?? null) === 'Clients')
            ? ($segments[2] ?? Str::studly($this->client))
            : Str::studly($this->client);

        if (class_exists($clientProvider)) {
            $this->app->register($clientProvider);
        }
    }

    public function boot(): void
    {
        // Overlay the active client's views so they shadow core views of the same
        // name (BAN-179). Mutate the *view factory's own* finder via getFinder():
        // 'view.finder' is a non-singleton binding, so make('view.finder') would
        // return a throwaway instance the factory never uses. prependLocation()
        // puts the overlay ahead of core, giving genuine shadowing.
        $overlayViews = base_path("app/Clients/{$this->clientDir}/resources/views");
        if (is_dir($overlayViews)) {
            $this->app->make('view')->getFinder()->prependLocation($overlayViews);
        }
    }
}
