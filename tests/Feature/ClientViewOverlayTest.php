<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClientViewOverlayTest extends TestCase
{
    /**
     * The DriveDesk overlay directory is StudlyCase (`DriveDesk`) while
     * APP_CLIENT is the lowercase slug (`drivedesk`). ClientServiceProvider
     * must register the overlay path with the EXACT on-disk casing, otherwise it
     * silently no-ops on case-sensitive filesystems (Linux prod) while appearing
     * to work on case-insensitive ones (Windows dev) — the BAN-179 regression that
     * produced Sentry DIRECTONDERWEG-3 ("View [client.layouts.partials.offcanvas]
     * not found"). Assert the literal, case-sensitive path so this fails on every
     * OS, not just the case-sensitive ones.
     */
    public function test_client_overlay_view_path_is_registered_with_exact_casing(): void
    {
        $this->bootClient('drivedesk');

        $paths = array_map(
            fn (string $p) => str_replace('\\', '/', $p),
            $this->app->make('view')->getFinder()->getPaths()
        );

        $expected = str_replace('\\', '/', base_path('app/Clients/DriveDesk/resources/views'));

        $this->assertContains(
            $expected,
            $paths,
            'The DriveDesk view overlay must be registered with exact StudlyCase casing.'
        );
    }

    /**
     * The partial BAN-179 broke on must resolve through the normal view name
     * with the overlay in place. It lives in core now (the repo split promoted
     * it), so this pins that the overlay does not hide it either.
     */
    public function test_storefront_partial_resolves_with_the_overlay_registered(): void
    {
        $this->bootClient('drivedesk');

        $this->assertTrue(
            $this->app->make('view')->exists('client.layouts.partials.offcanvas'),
            'client.layouts.partials.offcanvas should be findable.'
        );
    }

    /**
     * Drive ClientServiceProvider through its real register() + boot() lifecycle
     * for the given client. register() resolves the overlay directory; boot()
     * adds it to the live view finder.
     */
    private function bootClient(string $client): void
    {
        config(['app.client' => $client]);

        $provider = new \App\Providers\ClientServiceProvider($this->app);
        $provider->register();
        $provider->boot();
    }
}
