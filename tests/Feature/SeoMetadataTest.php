<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AsInstalledApp;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Server-rendered SEO metadata (BAN-262).
 *
 * SSR is off by design, so anything React sets client-side does not exist for a
 * crawler that cannot run JavaScript — and social scrapers never can. These
 * assertions run against the raw HTML on purpose: asserting on the rendered DOM
 * would pass while the thing being fixed stayed broken.
 */
class SeoMetadataTest extends TestCase
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

    // ── The demo gateway is the indexable page ────────────────────────────────

    public function test_home_ships_a_title_and_description_in_the_raw_html(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title inertia>DriveDesk — Car Rental Management Software</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="Run your car rental agency', $html);
    }

    public function test_home_ships_open_graph_and_twitter_tags(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        foreach (['og:type', 'og:title', 'og:url', 'og:description', 'og:site_name'] as $tag) {
            $this->assertStringContainsString($tag, $html, "missing {$tag}");
        }

        $this->assertStringContainsString('twitter:card', $html);
    }

    public function test_a_missing_og_image_asset_emits_no_image_tag(): void
    {
        // An og:image pointing at a 404 makes LinkedIn and Slack render a broken
        // card instead of falling back to the text-only one, so the configured
        // path is only honoured once the file actually exists.
        $this->asClient('drivedesk');
        config(['client.seo.og_image' => '/images/does-not-exist.png']);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('og:image', $html);
        $this->assertStringContainsString('content="summary"', $html);
    }

    public function test_an_existing_og_image_asset_is_emitted_as_a_large_card(): void
    {
        $this->asClient('drivedesk');
        config(['client.seo.og_image' => '/images/hero-login.jpg']);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('og:image', $html);
        $this->assertStringContainsString('summary_large_image', $html);
    }

    public function test_home_is_canonical_and_indexable(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        $this->assertMatchesRegularExpression('#<link rel="canonical" href="https?://[^"]+">#', $html);
        $this->assertStringNotContainsString('noindex', $html);
    }

    public function test_home_carries_organization_and_software_schema(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('SoftwareApplication', $html);
        $this->assertStringContainsString('Organization', $html);
    }

    // ── Everything else must not be indexed ──────────────────────────────────

    public function test_login_is_noindex(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringNotContainsString('og:title', $html);
    }

    public function test_admin_pages_are_noindex(): void
    {
        $this->asClient('drivedesk');
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $html = $this->actingAs($owner)->get('/dashboard')->getContent();

        $this->assertStringContainsString('noindex', $html);
    }

    public function test_home_is_noindex_for_a_client_without_the_gateway(): void
    {
        // directonderweg is internal-only; "/" redirects to login there.
        $this->asClient('directonderweg');

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('noindex', $html);
    }

    // ── Language and direction ───────────────────────────────────────────────

    public function test_arabic_locales_declare_rtl_and_a_real_language_code(): void
    {
        $this->asClient('drivedesk');

        // `ary` is the app's own switcher code for Moroccan Arabic, but the copy
        // under it is Modern Standard Arabic — declaring `ary` would tell search
        // engines the page is in a language it is not.
        $html = $this->withSession(['locale' => 'ary'])->get('/')->getContent();

        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html);
        $this->assertStringNotContainsString('lang="ary"', $html);
    }

    public function test_latin_locales_declare_ltr(): void
    {
        $this->asClient('drivedesk');

        $html = $this->withSession(['locale' => 'fr'])->get('/')->getContent();

        $this->assertStringContainsString('<html lang="fr" dir="ltr">', $html);
    }

    // ── The title suffix ─────────────────────────────────────────────────────

    public function test_the_app_name_meta_carries_the_client_name_not_the_template(): void
    {
        // app.jsx reads this for the title suffix; it used to fall back to the
        // white-label template name and render "… - RentCar" publicly.
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('<meta name="app-name" content="DriveDesk">', $html);
    }

    // ── Ziggy payload ────────────────────────────────────────────────────────

    public function test_installer_routes_are_not_published_to_the_browser(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('LaravelInstaller::', $html);
        $this->assertStringNotContainsString('LaravelUpdater::', $html);
    }

    public function test_routes_the_spa_actually_calls_survive_the_filter(): void
    {
        // Guards the Ziggy `except` list: over-filtering breaks route() at
        // runtime, which fails silently until a user clicks the thing.
        $this->asClient('drivedesk');

        $html = $this->get('/login')->getContent();

        foreach (['"login"', '"logout"', '"password.request"'] as $route) {
            $this->assertStringContainsString($route, $html, "Ziggy dropped {$route}");
        }
    }

    public function test_public_pages_ship_only_the_routes_they_need(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        // Everything DemoGateway.jsx calls route() for must be present…
        foreach (['"login"', '"demo.request"'] as $route) {
            $this->assertStringContainsString($route, $html, "public Ziggy group dropped {$route}");
        }

        // …and the admin surface must not be.
        foreach (['"booking.index"', '"vehicle.index"', '"tva.index"'] as $route) {
            $this->assertStringNotContainsString($route, $html, "admin route {$route} leaked to a public page");
        }
    }

    public function test_the_storefront_landing_keeps_its_vehicle_detail_route(): void
    {
        // Landing.jsx links every vehicle card through route('client.details').
        $this->asClient('directonderweg');

        $html = $this->get('/landing')->getContent();

        $this->assertStringContainsString('"client.details"', $html);
    }

    public function test_the_app_still_gets_the_full_route_list(): void
    {
        $this->asClient('drivedesk');
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $html = $this->actingAs($owner)->get('/dashboard')->getContent();

        $this->assertStringContainsString('"booking.index"', $html);
    }

    // ── sitemap.xml ──────────────────────────────────────────────────────────

    public function test_sitemap_lists_the_gateway_and_omits_the_removed_storefront(): void
    {
        $this->asClient('drivedesk');

        $response = $this->get('/sitemap.xml')->assertOk();
        $xml      = $response->getContent();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertMatchesRegularExpression('#<loc>https?://[^/]+/</loc>#', $xml);
        // BAN-261 removed the storefront for this client; listing it would point
        // crawlers at a 404.
        $this->assertStringNotContainsString('/landing', $xml);
    }

    public function test_sitemap_lists_the_storefront_for_clients_that_keep_it(): void
    {
        $this->asClient('directonderweg');

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/landing', $xml);
    }

    public function test_sitemap_is_404_when_the_client_has_no_public_pages(): void
    {
        $this->asClient('directonderweg');
        config(['features.public_storefront' => false, 'features.demo_gateway' => false]);

        // An empty sitemap tells Google the site has no indexable pages, which
        // is worse than not having one.
        $this->get('/sitemap.xml')->assertNotFound();
    }

    // ── llms.txt ─────────────────────────────────────────────────────────────

    public function test_llms_txt_describes_the_product(): void
    {
        $this->asClient('drivedesk');

        $response = $this->get('/llms.txt')->assertOk();

        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('# DriveDesk', $response->getContent());
    }

    public function test_llms_txt_is_404_without_a_public_product_page(): void
    {
        $this->asClient('directonderweg');

        $this->get('/llms.txt')->assertNotFound();
    }
}
