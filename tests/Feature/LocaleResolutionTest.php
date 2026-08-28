<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-262: drivedesk defaults anonymous/guest visitors to Moroccan Arabic
 * (Darija, 'ary') via its public_default_locale, loading lang/ary.json. Other
 * clients (the acme fixture) are unset and keep their existing 'fr' default, so
 * their observable behaviour is unchanged. Locale resolution is exercised via
 * the guest login page (shared 'locale'/'translations' props) — the same
 * SetLocale + translation pipeline the marketing landing uses.
 */
class LocaleResolutionTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    public function test_drivedesk_guest_defaults_to_moroccan_arabic(): void
    {
        $this->asClient('drivedesk');

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', 'ary')
                // Proves lang/ary.json is the resolved bundle, not the English fallback.
                ->where('translations.dg_book', 'احجز عرضاً توضيحياً')
            );
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
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'ary'));
    }

    public function test_explicit_ary_language_switch_is_accepted(): void
    {
        $this->asClient('drivedesk');

        // The /language/{lang} switch stores the locale; the next request resolves it.
        $this->get('/language/ary');

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'ary'));
    }
}
