<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\AsInstalledApp;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * The public B2C rental storefront (BAN-261).
 *
 * `/landing` and the pages its layout partials link to serve renters: a fleet
 * list, a booking widget, contact and search.
 *
 * BAN-261 gated the family off for drivedesk, because those pages targeted the
 * opposite audience from the one DriveDesk sells to. BAN-329 turned it back on
 * for that client: the storefront now runs *beside* the B2B demo gateway rather
 * than instead of it -- `/` still renders DemoGateway, which the tests below
 * pin, so the two publics do not collide.
 *
 * The gate itself is what these tests are about, and it is still a gate: the
 * flag alone decides, never APP_CLIENT (§10.2 rule 1), and a client that turns
 * it off gets 404s across the whole family.
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

    /**
     * The family disappears together. Forced off rather than read off a client
     * (§10.2 rule 6) -- no client ships it off today, and this is about the
     * gate, not about who happens to be using it.
     */
    #[DataProvider('storefrontRoutes')]
    public function test_the_whole_storefront_family_404s_when_the_flag_is_off(string $method, string $uri): void
    {
        $this->asClient('drivedesk');
        config(['client.features.public_storefront' => false]);

        $this->{$method}($uri)->assertNotFound();
    }

    /** BAN-329: and it is on for drivedesk, so the family answers there. */
    #[DataProvider('storefrontRoutes')]
    public function test_the_whole_storefront_family_answers_for_drivedesk(string $method, string $uri): void
    {
        $this->asClient('drivedesk');

        $response = $this->{$method}($uri);

        $this->assertNotSame(404, $response->getStatusCode(), "{$method} {$uri} is still gated off");
    }

    public function test_landing_still_serves_clients_that_keep_the_storefront(): void
    {
        // acme keeps the storefront — the flag defaults to on.
        $this->asClient('acme');

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

    /**
     * The one that matters most after BAN-329. DriveDesk's public face is the
     * B2B gateway at /, and turning the storefront on must not take it: the
     * root belongs to demo_gateway, checked first in HomeController::index().
     */
    public function test_the_storefront_does_not_take_the_root_from_the_demo_gateway(): void
    {
        $this->asClient('drivedesk');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/DemoGateway'));
    }

    public function test_the_storefront_leaves_login_reachable(): void
    {
        $this->asClient('drivedesk');

        $this->get('/login')->assertOk();
    }

    /**
     * / stays a login redirect for a client with no demo gateway, even one that
     * runs the storefront. public_storefront defaults to true, so serving the
     * storefront at / would change the root URL of every deployment except
     * drivedesk on upgrade, with no opt-in (CLAUDE.md 10.2 rule 2). The
     * storefront home lives at /landing.
     */
    public function test_root_stays_a_login_redirect_for_a_storefront_client(): void
    {
        $this->asClient('acme');
        config(['client.features.public_storefront' => true]);

        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_landing_hides_vehicles_marked_unavailable_for_rent(): void
    {
        $this->asClient('acme');
        $available = Vehicle::factory()->create(['available_for_rent' => true]);
        $hidden    = Vehicle::factory()->create(['available_for_rent' => false]);

        $this->get('/landing')->assertInertia(fn (Assert $page) => $page
            ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->contains($available->id)
                && collect($vehicles)->pluck('id')->doesntContain($hidden->id))
        );
    }

    // ── heroImage: single banner, desktop/mobile variants ─────────────────────

    public function test_hero_image_falls_back_to_the_single_upload_for_both_devices(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('upload/home/1_image_home_1.png', 'fake');
        Setting::create(['name' => 'image_home_1', 'value' => '1_image_home_1.png', 'parent_id' => 1]);
        flushSettingsCache();

        $this->asClient('acme');

        $this->get('/landing')->assertInertia(fn (Assert $page) => $page
            ->where('heroImage.desktop', fn ($url) => str_ends_with($url, '1_image_home_1.png'))
            ->where('heroImage.mobile', fn ($url) => str_ends_with($url, '1_image_home_1.png'))
        );
    }

    public function test_hero_image_prefers_the_device_specific_variant_when_set(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('upload/home/1_image_home_1.png', 'fake');
        Storage::disk('public')->put('upload/home/1_image_home_1_desktop.png', 'fake');
        Storage::disk('public')->put('upload/home/1_image_home_1_mobile.png', 'fake');
        Setting::create(['name' => 'image_home_1', 'value' => '1_image_home_1.png', 'parent_id' => 1]);
        Setting::create(['name' => 'image_home_1_desktop', 'value' => '1_image_home_1_desktop.png', 'parent_id' => 1]);
        Setting::create(['name' => 'image_home_1_mobile', 'value' => '1_image_home_1_mobile.png', 'parent_id' => 1]);
        flushSettingsCache();

        $this->asClient('acme');

        $this->get('/landing')->assertInertia(fn (Assert $page) => $page
            ->where('heroImage.desktop', fn ($url) => str_ends_with($url, '1_image_home_1_desktop.png'))
            ->where('heroImage.mobile', fn ($url) => str_ends_with($url, '1_image_home_1_mobile.png'))
        );
    }
}
