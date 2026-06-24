<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClientViewOverlayTest extends TestCase
{
    /**
     * The DirectOnderweg overlay directory is StudlyCase (`DirectOnderweg`) while
     * APP_CLIENT is the lowercase slug (`directonderweg`). ClientServiceProvider
     * must register the overlay path with the EXACT on-disk casing, otherwise it
     * silently no-ops on case-sensitive filesystems (Linux prod) while appearing
     * to work on case-insensitive ones (Windows dev) — the BAN-179 regression that
     * produced Sentry DIRECTONDERWEG-3 ("View [client.layouts.partials.offcanvas]
     * not found"). Assert the literal, case-sensitive path so this fails on every
     * OS, not just the case-sensitive ones.
     */
    public function test_client_overlay_view_path_is_registered_with_exact_casing(): void
    {
        $this->bootClient('directonderweg');

        $paths = array_map(
            fn (string $p) => str_replace('\\', '/', $p),
            $this->app->make('view')->getFinder()->getPaths()
        );

        $expected = str_replace('\\', '/', base_path('app/Clients/DirectOnderweg/resources/views'));

        $this->assertContains(
            $expected,
            $paths,
            'The DirectOnderweg view overlay must be registered with exact StudlyCase casing.'
        );
    }

    /**
     * The partial that BAN-179 moved into the overlay must resolve through the
     * normal view name. This is the user-visible behaviour that broke.
     */
    public function test_overlay_only_partial_resolves(): void
    {
        $this->bootClient('directonderweg');

        $this->assertTrue(
            $this->app->make('view')->exists('client.layouts.partials.offcanvas'),
            'The overlay-only partial client.layouts.partials.offcanvas should be findable.'
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
