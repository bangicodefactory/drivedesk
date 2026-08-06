<?php

namespace Tests\Feature;

use App\Support\Locales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AsInstalledApp;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Locale-prefixed public URLs and hreflang (BAN-263).
 *
 * Locale used to live only in the session, so all five languages shared one URL
 * and a crawler — which carries no session — only ever saw the client's guest
 * default. hreflang could not be expressed at all. Public pages are now also
 * served under /fr, /en, /ar, with "/" as the x-default.
 */
class LocaleUrlTest extends TestCase
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

    // ── The prefix actually switches language ────────────────────────────────

    public function test_each_locale_prefix_serves_that_language(): void
    {
        $this->asClient('drivedesk');

        $this->get('/fr')->assertOk()->assertSee('lang="fr"', false);
        $this->get('/en')->assertOk()->assertSee('lang="en"', false);
        // `ar` is Modern Standard Arabic, which is what the copy actually is.
        $this->get('/ar')->assertOk()->assertSee('lang="ar" dir="rtl"', false);
    }

    public function test_the_unprefixed_home_still_serves_the_guest_default(): void
    {
        $this->asClient('drivedesk');

        // drivedesk's public_default_locale is `ary`, which declares as `ar`.
        $this->get('/')->assertOk()->assertSee('lang="ar"', false);
    }

    public function test_the_url_beats_a_logged_in_users_saved_language(): void
    {
        // The auth branch used to overwrite unconditionally, which would have
        // made the prefix a no-op for anyone signed in.
        $this->asClient('drivedesk');
        $user = User::factory()->create(['type' => 'owner', 'parent_id' => 0, 'lang' => 'en']);

        $this->actingAs($user)->get('/fr')->assertSee('lang="fr"', false);
    }

    public function test_an_unsupported_locale_is_not_a_route(): void
    {
        $this->asClient('drivedesk');

        // `nl` is in drivedesk's supported_locales but SetLocale cannot serve
        // it, so publishing /nl would advertise a URL serving the wrong language.
        $this->get('/nl')->assertNotFound();
        // `ary` is excluded as a duplicate of `ar`.
        $this->get('/ary')->assertNotFound();
        $this->get('/zz')->assertNotFound();
    }

    // ── The prefix must not swallow real paths ───────────────────────────────

    public function test_literal_paths_are_not_captured_by_the_locale_route(): void
    {
        // A `/{locale}` route with a loose constraint would eat every
        // single-segment URL on the site.
        $this->asClient('drivedesk');

        $this->get('/login')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/llms.txt')->assertOk();
    }

    public function test_the_storefront_landing_is_not_captured_either(): void
    {
        $this->asClient('directonderweg');

        $this->get('/landing')->assertOk();
    }

    // ── hreflang ─────────────────────────────────────────────────────────────

    public function test_the_gateway_declares_every_locale_and_an_x_default(): void
    {
        $this->asClient('drivedesk');

        $html = $this->get('/')->getContent();

        foreach (Locales::forPublicUrls() as $locale) {
            $this->assertMatchesRegularExpression(
                '#<link rel="alternate" hreflang="'.$locale.'" href="https?://[^"]+/'.$locale.'">#',
                $html,
                "missing hreflang for {$locale}"
            );
        }

        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_locale_pages_carry_the_same_alternate_set(): void
    {
        // Every variant must cross-reference every other, x-default included,
        // or Google ignores the cluster.
        $this->asClient('drivedesk');

        $html = $this->get('/fr')->getContent();

        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="ar"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_each_locale_page_canonicalises_to_itself(): void
    {
        $this->asClient('drivedesk');

        // Host-agnostic: APP_URL differs between .env and CI's .env.example.
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="https?://[^"]+/fr">#',
            $this->get('/fr')->getContent()
        );
    }

    public function test_pages_without_locale_urls_emit_no_hreflang(): void
    {
        // The storefront has no locale variants yet; a lone self-referencing
        // hreflang would be noise.
        $this->asClient('directonderweg');

        $html = $this->get('/landing')->getContent();

        $this->assertStringNotContainsString('hreflang', $html);
    }

    // ── Clients without a public product page ────────────────────────────────

    public function test_locale_urls_do_not_exist_without_the_gateway(): void
    {
        // directonderweg is internal-only; /fr there would just bounce to login.
        $this->asClient('directonderweg');

        $this->get('/fr')->assertNotFound();
    }

    // ── Sitemap ──────────────────────────────────────────────────────────────

    public function test_the_sitemap_lists_every_locale_url(): void
    {
        $this->asClient('drivedesk');

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach (Locales::forPublicUrls() as $locale) {
            $this->assertStringContainsString("/{$locale}</loc>", $xml, "sitemap missing /{$locale}");
        }
    }

    // ── The locale set itself ────────────────────────────────────────────────

    public function test_public_locales_exclude_what_the_app_cannot_serve(): void
    {
        $this->asClient('drivedesk');

        $locales = Locales::forPublicUrls();

        $this->assertSame(['en', 'fr', 'ar'], $locales);
        $this->assertNotContains('nl', $locales);
        $this->assertNotContains('ary', $locales);
    }
}
