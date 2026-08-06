<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\AsInstalledApp;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * The public B2C rental storefront (BAN-261).
 *
 * `/landing` and the pages its layout partials link to serve renters: a fleet
 * list, a booking widget, contact and search. DriveDesk sells the platform *to*
 * rental agencies, so on that client the storefront targeted the opposite
 * audience — and shipped seeded demo vehicles plus invented testimonials on a
 * live commercial domain. It is gated off there and 404s.
 *
 * Every other client keeps it, which is today's behavior (CLAUDE.md §10.2 rule 2).
 */
class PublicStorefrontTest extends TestCase
{
    use AsInstalledApp;
    use RefreshDatabase;
    use WithClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markAppInstalled();
    }

    protected function tearDown(): void
    {
        $this->removeInstalledMarkerIfCreated();
        parent::tearDown();
    }

    /** Every route in the storefront family, as [method, uri]. */
    public static function storefrontRoutes(): array
    {
        return [
            'landing'  => ['get', '/landing'],
            'contact'  => ['get', '/contact'],
            'search'   => ['get', '/search'],
            'newsletter' => ['post', '/newsletter/subscribe'],
        ];
    }

    #[DataProvider('storefrontRoutes')]
    public function test_storefront_is_404_for_drivedesk(string $method, string $uri): void
    {
        $this->asClient('drivedesk');

        $this->{$method}($uri)->assertNotFound();
    }

    public function test_landing_still_serves_clients_that_keep_the_storefront(): void
    {
        // directonderweg is unchanged by BAN-261 — the flag defaults to on.
        $this->asClient('directonderweg');

        $this->get('/landing')->assertOk();
    }

    public function test_the_flag_alone_decides_visibility(): void
    {
        // Guards against the gate being wired to APP_CLIENT rather than the
        // feature — an inline client check is exactly what §10.2 rule 1 forbids.
        $this->asClient('drivedesk');
        config(['features.public_storefront' => true]);

        $this->get('/landing')->assertOk();

        config(['features.public_storefront' => false]);

        $this->get('/landing')->assertNotFound();
    }

    public function test_removing_the_storefront_leaves_the_demo_gateway_intact(): void
    {
        // DriveDesk's public face is the B2B gateway at /, which must survive.
        $this->asClient('drivedesk');

        $this->get('/')->assertOk();
    }

    public function test_removing_the_storefront_leaves_login_reachable(): void
    {
        $this->asClient('drivedesk');

        $this->get('/login')->assertOk();
    }
}
