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

        // The whole <html> tag, not a bare lang="xx". Every one of these pages
        // also emits <link rel="alternate" hreflang="fr" ...>, and
        // `hreflang="fr"` *contains* `lang="fr"` -- so the substring form was
        // satisfied by the alternate link no matter what language the document
        // was actually in.
        $this->get('/fr')->assertOk()->assertSee('<html lang="fr" dir="ltr">', false);
        $this->get('/en')->assertOk()->assertSee('<html lang="en" dir="ltr">', false);
        // `ar` is Modern Standard Arabic, which is what the copy actually is.
        $this->get('/ar')->assertOk()->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_the_unprefixed_home_still_serves_the_guest_default(): void
    {
        $this->asClient('drivedesk');

        // drivedesk's public_default_locale is `fr` since BAN-330. Asserted on
        // the whole <html> tag: see the note above -- the bare substring is
        // matched by this page's own hreflang="fr" alternate, so it passed even
        // when the document was English. This is the assertion the locale
        // change hangs on, and it could not fail.
        $this->get('/')->assertOk()->assertSee('<html lang="fr" dir="ltr">', false);
    }

    public function test_a_signed_in_visitor_keeps_their_own_language(): void
    {
        // The prefix is for guests and crawlers. For a signed-in visitor the XSS
        // route middleware re-asserts Auth::user()->lang app-wide, and that wins
        // — they also get the Dashboard at this URL, not the marketing page, so
        // their own language is the right answer. Pinned because it is a real
        // limit of the feature, not an accident.
        $this->asClient('drivedesk');
        $user = User::factory()->create(['type' => 'owner', 'parent_id' => 0, 'lang' => 'en']);

        $this->actingAs($user)->get('/fr')->assertSee('<html lang="en" dir="ltr">', false);
    }

    public function test_the_url_wins_for_a_guest_with_a_session_language(): void
    {
        // The case that actually matters for indexing: no account, and a session
        // locale that disagrees with the URL.
        $this->asClient('drivedesk');

        $this->withSession(['locale' => 'en'])->get('/fr')->assertSee('<html lang="fr" dir="ltr">', false);
    }

    public function test_an_unsupported_locale_is_not_a_route(): void
    {
        $this->asClient('drivedesk');

        // `nl` left drivedesk's supported_locales in BAN-330 and SetLocale could
        // never serve it anyway, so publishing /nl would advertise a URL serving
        // the wrong language.
        $this->get('/nl')->assertNotFound();
        // `ary` is excluded as a duplicate of `ar` -- still true, and still
        // independent of the list: Locales::forPublicUrls() filters it by name.
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
        $this->asClient('acme');

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
        $this->asClient('acme');

        $html = $this->get('/landing')->getContent();

        $this->assertStringNotContainsString('hreflang', $html);
    }

    // ── Clients without a public product page ────────────────────────────────

    public function test_locale_urls_do_not_exist_without_the_gateway(): void
    {
        // acme is internal-only; /fr there would just bounce to login.
        $this->asClient('acme');

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

    public function test_every_public_locale_actually_has_the_gateway_copy(): void
    {
        // The gateway's dg_* keys lived only in ary.json, so
        // t('dg_…', 'English default') fell back to the inline English and /fr
        // and /en rendered identical pages — hreflang advertising three URLs
        // with the same content, which Google discounts. Structure alone is not
        // enough; a locale URL has to serve that locale's words.
        $this->asClient('drivedesk');

        $keys = fn (string $locale) => array_keys(array_filter(
            json_decode(file_get_contents(resource_path("lang/{$locale}.json")), true) ?: [],
            fn ($key) => str_starts_with($key, 'dg_'),
            ARRAY_FILTER_USE_KEY
        ));

        $reference = $keys('en');
        $this->assertNotEmpty($reference, 'en.json has no gateway copy');

        foreach (Locales::forPublicUrls() as $locale) {
            $this->assertEqualsCanonicalizing(
                $reference,
                $keys($locale),
                "{$locale}.json is missing gateway copy, so /{$locale} would render English"
            );
        }
    }

    public function test_each_locale_serves_its_own_words_not_a_fallback(): void
    {
        // Guards the same thing end to end: two locale URLs returning the same
        // headline means the hreflang cluster is decorative.
        $this->asClient('drivedesk');

        // The headline is rendered client-side, so assert on the translation
        // payload the page ships rather than on markup that does not exist yet.
        $copy = function (string $path) {
            $html = $this->get($path)->getContent();
            preg_match('/"dg_hero_line1":"(.*?)"/', $html, $m);

            return $m[1] ?? '';
        };

        $en = $copy('/en');
        $fr = $copy('/fr');

        // Both must be present *and* differ. Asserting only that they differ
        // passes when a key is missing entirely — which is the bug, not the fix.
        $this->assertNotSame('', $en, '/en ships no hero copy');
        $this->assertNotSame('', $fr, '/fr ships no hero copy');
        $this->assertNotSame($en, $fr, '/en and /fr ship the same hero copy');
    }

    public function test_public_locales_exclude_what_the_app_cannot_serve(): void
    {
        $this->asClient('drivedesk');

        $locales = Locales::forPublicUrls();

        // Order follows the client's own list, which BAN-330 rewrote to
        // fr,ar,en.
        $this->assertSame(['fr', 'ar', 'en'], $locales);
        $this->assertNotContains('nl', $locales);
        $this->assertNotContains('ary', $locales);
    }

    /**
     * The SetLocale::SUPPORTED intersection, which drivedesk stopped exercising
     * the moment BAN-330 took `nl` out of its list: with fr/ar/en all servable,
     * deleting that filter from forPublicUrls() would keep every drivedesk
     * assertion green. acme still lists `nl`, so it is the client that can
     * still tell the difference.
     */
    public function test_a_client_listing_an_unservable_locale_does_not_publish_it(): void
    {
        $this->asClient('acme');

        $this->assertContains('nl', config('client.supported_locales'), 'fixture no longer lists nl');
        $this->assertNotContains('nl', Locales::forPublicUrls());
    }
}
