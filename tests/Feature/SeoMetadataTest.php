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

    /**
     * Write a settings row for the deployment's owner and drop the cache.
     *
     * The owner, not parent_id = 1: every settings write goes through
     * parentId(), so an owner's rows carry their own id and nothing has ever
     * written a row under 1.
     */
    /**
     * Write a global settings row and drop its cache entry.
     *
     * parent_id = 1, because that is where a guest reads and where
     * ClientInstall seeds each client's branding -- its own comment calls it
     * the home of "all global/admin settings". An owner's rows live under the
     * owner's id and are not what the public pages render.
     */
    private function putGlobalSetting(string $name, string $value): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['name' => $name, 'parent_id' => 1],
            ['value' => $value]
        );

        \Illuminate\Support\Facades\Cache::forget('settings_1');
    }

    // ── BAN-314: the Site SEO settings actually reach a page ──────────────
    //
    // settingsKeys() has carried meta_seo_title / _description / _image since
    // forever, SettingController writes all three, and ClientInstall seeds
    // meta_seo_title per client -- but nothing read them. Seo::forRequest(),
    // Seo's organization schema and SeoController all took
    // config('client.seo') alone, so the Site SEO screen was write-only and
    // every deployment emitted DriveDesk's own marketing copy.

    public function test_the_seo_title_setting_beats_the_client_config(): void
    {
        $this->asClient('drivedesk');
        $this->putGlobalSetting('meta_seo_title', 'Agence Atlas — Location de voitures');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title inertia>Agence Atlas — Location de voitures</title>', $html);
    }

    public function test_the_seo_description_setting_beats_the_client_config(): void
    {
        $this->asClient('drivedesk');
        $this->putGlobalSetting('meta_seo_description', 'Louez une voiture a Marrakech.');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('content="Louez une voiture a Marrakech."', $html);
    }

    /** A blank row is not a choice: every deployment has one for every key. */
    public function test_a_blank_seo_setting_falls_back_to_the_client_config(): void
    {
        $this->asClient('drivedesk');
        $this->putGlobalSetting('meta_seo_title', '   ');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title inertia>DriveDesk — Car Rental Management Software</title>', $html);
    }

    /** app_name is the deployment's own brand, so it drives og:site_name too. */
    public function test_the_app_name_setting_drives_the_site_name(): void
    {
        $this->asClient('drivedesk');
        $this->putGlobalSetting('app_name', 'Agence Atlas');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Agence Atlas', $html);
    }

    /**
     * SettingController stores the upload as a bare filename on the public
     * disk, so the setting is not a URL the way the config value is.
     */
    public function test_the_seo_image_setting_resolves_under_the_public_disk(): void
    {
        $this->asClient('drivedesk');
        $this->putGlobalSetting('meta_seo_image', 'atlas-og.png');

        // Seo::image() gates on file_exists(public_path(...)), so the asset has
        // to sit where the storage symlink puts it in production.
        $dir = public_path('storage/upload/seo');
        @mkdir($dir, 0777, true);
        file_put_contents($dir.'/atlas-og.png', 'x');

        try {
            $html = $this->get('/')->assertOk()->getContent();
            $this->assertStringContainsString('/storage/upload/seo/atlas-og.png', $html);
        } finally {
            @unlink($dir.'/atlas-og.png');
        }
    }

    /**
     * A setting naming a file that is not there must leave the config image
     * alone, not destroy it. public/storage is a symlink the cPanel target does
     * not always have, and an upload can be removed later -- overriding
     * unconditionally would emit no og:image at all where the config one works.
     */
    public function test_a_missing_seo_image_setting_keeps_the_config_image(): void
    {
        $this->asClient('drivedesk');
        config(['client.seo.og_image' => '/images/hero-login.jpg']);
        $this->putGlobalSetting('meta_seo_image', 'not-uploaded.png');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('not-uploaded.png', $html);
        $this->assertStringContainsString('og:image', $html);
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

    // ── Absolute URLs come from config, not the request ──────────────────────

    public function test_a_spoofed_host_header_cannot_choose_the_canonical(): void
    {
        // TrustHosts is disabled in app/Http/Kernel, so `Host` is attacker
        // controlled. Deriving canonical/og:url/hreflang from it lets a spoofed
        // header point them at another domain — cache-poisonable, and a canonical
        // is the worst possible tag to hand over.
        $this->asClient('drivedesk');

        // Absolute URL, not a Host header: Laravel's test client builds the
        // request from its own base URL, so a header never reaches
        // getSchemeAndHttpHost() and the assertion would pass with or without
        // the fix.
        $html = $this->get('http://evil.example.com/')->getContent();

        // Scoped to the tags this PR owns. Vite's asset URLs and Ziggy's `url`
        // are also host-derived, but that is pre-existing framework behaviour
        // and the real fix for it is enabling TrustHosts — tracked separately.
        $this->assertStringContainsString('<link rel="canonical" href="'.rtrim(config('app.url'), '/').'/">', $html);
        $this->assertStringNotContainsString('rel="canonical" href="http://evil', $html);
        $this->assertStringNotContainsString('property="og:url" content="http://evil', $html);
        $this->assertStringNotContainsString('hreflang="x-default" href="http://evil', $html);
    }

    public function test_an_unset_app_url_does_not_send_canonicals_to_localhost(): void
    {
        // config/app.php defaults `url` to the truthy string 'http://localhost',
        // so a plain `?:` fallback never fires and every canonical, hreflang and
        // sitemap entry would silently point at localhost in production —
        // invisible to the deploy smoke test, which curls vars.APP_URL directly.
        $this->asClient('drivedesk');
        config(['app.url' => 'http://localhost']);

        $html = $this->get('http://drivedesk.ma/')->getContent();

        $this->assertStringNotContainsString('href="http://localhost/"', $html);
        $this->assertStringContainsString('<link rel="canonical" href="http://drivedesk.ma/">', $html);
    }

    public function test_an_empty_app_url_falls_back_to_the_request_host(): void
    {
        $this->asClient('drivedesk');
        config(['app.url' => '']);

        $html = $this->get('http://drivedesk.ma/')->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="http://drivedesk.ma/">', $html);
    }

    public function test_a_configured_app_url_still_beats_a_spoofed_host(): void
    {
        // The fallback must not become a way back in for the Host header.
        $this->asClient('drivedesk');
        config(['app.url' => 'https://drivedesk.ma']);

        $html = $this->get('http://evil.example.com/')->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="https://drivedesk.ma/">', $html);
        $this->assertStringNotContainsString('rel="canonical" href="http://evil', $html);
    }

    public function test_a_spoofed_host_cannot_choose_the_sitemap_urls(): void
    {
        $this->asClient('drivedesk');

        $xml = $this->get('http://evil.example.com/sitemap.xml')->getContent();

        $this->assertStringNotContainsString('evil.example.com', $xml);
    }

    // ── Everything else must not be indexed ──────────────────────────────────

    public function test_an_authenticated_visitor_at_the_root_is_not_treated_as_public(): void
    {
        // HomeController@index returns the Dashboard for a signed-in user at "/".
        // Marking that indexable also selected the trimmed public Ziggy group, so
        // the dashboard rendered with 7 routes and its sidebar's
        // route('booking.index') threw client-side.
        $this->asClient('drivedesk');
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $html = $this->actingAs($owner)->get('/')->getContent();

        $this->assertStringContainsString('noindex', $html);
        $this->assertStringNotContainsString('og:title', $html);
        $this->assertStringContainsString('"booking.index"', $html, 'the dashboard lost the routes it needs');
    }

    public function test_an_authenticated_visitor_on_a_locale_url_is_not_treated_as_public(): void
    {
        $this->asClient('drivedesk');
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $html = $this->actingAs($owner)->get('/fr')->getContent();

        $this->assertStringContainsString('noindex', $html);
        $this->assertStringContainsString('"booking.index"', $html);
    }

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
        // acme is internal-only; "/" redirects to login there.
        $this->asClient('acme');

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

    public function test_public_pages_ship_the_routes_reachable_without_a_page_load(): void
    {
        // A `public` Ziggy group once trimmed this page to 7 routes. It shipped
        // broken: @routes writes window.Ziggy once per *document*, and Inertia
        // navigates without one. Clicking "Log in" on the gateway is a
        // client-side visit, so Login.jsx rendered against the 7-route list and
        // route('password.request') threw — working only after a reload.
        //
        // So the marketing page must carry what the pages it links to need, and
        // login leads into the whole app.
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        // Named directly by DemoGateway.jsx…
        foreach (['"login"', '"demo.request"'] as $route) {
            $this->assertStringContainsString($route, $html, "Ziggy dropped {$route}");
        }

        // …and by Login.jsx, which is one client-side click away.
        foreach (['"password.request"', '"password.email"'] as $route) {
            $this->assertStringContainsString(
                $route,
                $html,
                "{$route} is missing, so clicking Log in without a page load throws"
            );
        }

        // The full list, since login opens onto the admin.
        $this->assertStringContainsString('"booking.index"', $html);
    }

    public function test_the_storefront_landing_keeps_its_vehicle_detail_route(): void
    {
        // Landing.jsx links every vehicle card through route('client.details').
        $this->asClient('acme');

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

    public function test_sitemap_lists_both_public_faces_for_drivedesk(): void
    {
        $this->asClient('drivedesk');

        $response = $this->get('/sitemap.xml')->assertOk();
        $xml      = $response->getContent();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        // The B2B demo gateway at the root.
        $this->assertMatchesRegularExpression('#<loc>https?://[^/]+/</loc>#', $xml);
        // And, since BAN-329, the storefront beside it. This assertion used to
        // be its inverse: BAN-261 had the storefront gated off here, and listing
        // a 404 would have sent crawlers at nothing.
        //
        // Worth stating plainly, because the sitemap is the difference between
        // "reachable by URL" and "offered to search engines": /landing still
        // renders four invented testimonials and a hardcoded five-star rating
        // per vehicle. Turning the flag on submits that page for indexing.
        $this->assertStringContainsString('/landing', $xml);
    }

    public function test_the_sitemap_omits_pages_that_do_not_use_this_shell(): void
    {
        // /contact and /search render client.layouts.app, so they carry no
        // canonical or robots directive — and /contact was 500ing on one client.
        $this->asClient('acme');

        $xml = $this->get('/sitemap.xml')->getContent();

        $this->assertStringNotContainsString('/contact', $xml);
        $this->assertStringNotContainsString('/search', $xml);
    }

    public function test_sitemap_lists_the_storefront_for_clients_that_keep_it(): void
    {
        $this->asClient('acme');

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/landing', $xml);
    }

    public function test_sitemap_is_404_when_the_client_has_no_public_pages(): void
    {
        $this->asClient('acme');
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
        $this->asClient('acme');

        $this->get('/llms.txt')->assertNotFound();
    }
}
