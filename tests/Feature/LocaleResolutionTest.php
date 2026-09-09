<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Which language an anonymous visitor gets.
 *
 * drivedesk defaulted guests to Moroccan Darija ('ary') until BAN-330 and now
 * defaults them to French; its languages are French, Arabic and English. Other
 * clients (the acme fixture) set no public_default_locale and fall back to 'fr'
 * on their own, so nothing about them changes either way.
 *
 * 'ary' itself is not withdrawn: SetLocale::SUPPORTED still serves it, so an
 * account that chose it keeps it and /language/ary still works. Only the guest
 * default moved, which is what the last test here pins.
 *
 * Resolution is exercised through the guest login page (shared
 * 'locale'/'translations' props) -- the same SetLocale + translation pipeline
 * the public pages use.
 */
class LocaleResolutionTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    public function test_drivedesk_guest_defaults_to_french(): void
    {
        $this->asClient('drivedesk');

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'fr')
                // Proves lang/fr.json is the resolved bundle, not the English
                // fallback -- the assertion the ary version of this test made,
                // pointed at the language that is now the default.
                ->where('translations.dg_book', 'Réserver une démo')
            );
    }

    /**
     * That the *client config* is what decides, not SetLocale's own fallback.
     *
     * Both are 'fr', so the test above cannot tell them apart: delete
     * public_default_locale from drivedesk.php and it still passes. Pointing
     * the config somewhere else and following it is the only assertion that
     * distinguishes them.
     */
    public function test_the_client_config_is_what_sets_the_guest_default(): void
    {
        $this->asClient('drivedesk');
        config(['client.public_default_locale' => 'ar']);

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'ar'));
    }

    public function test_a_client_without_a_public_default_locale_falls_back_to_french(): void
    {
        $this->asClient('acme');

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    public function test_invalid_locale_falls_back_to_client_default(): void
    {
        $this->asClient('drivedesk');

        $this->withSession(['locale' => 'zz-not-a-locale'])
            ->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    /**
     * ary is no longer the default; it is still servable. Somebody who chose it
     * -- or whose account still stores it -- must not silently lose it because
     * the guest default moved. This is the test that says dropping ary from
     * supported_locales was a change of default, not a withdrawal.
     */
    public function test_explicit_ary_language_switch_is_still_accepted(): void
    {
        $this->asClient('drivedesk');

        // The /language/{lang} switch stores the locale; the next request resolves it.
        $this->get('/language/ary');

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'ary'));
    }
}
