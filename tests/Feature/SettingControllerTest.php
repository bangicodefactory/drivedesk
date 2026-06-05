<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        $this->owner = User::factory()->create([
            'type'      => 'owner',
            'parent_id' => 0,
            'password'  => Hash::make('secret123'),
        ]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_account_page_requires_auth(): void
    {
        $this->get(route('setting.account'))->assertRedirect(route('login'));
    }

    public function test_password_page_requires_auth(): void
    {
        $this->get(route('setting.password'))->assertRedirect(route('login'));
    }

    public function test_company_page_requires_auth(): void
    {
        $this->get(route('setting.company'))->assertRedirect(route('login'));
    }

    // ── SettingController::accountData ────────────────────────────────────────

    public function test_account_data_updates_name_and_email(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.account'), [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $this->owner->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_account_data_uploads_profile_picture(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->owner)
            ->post(route('setting.account'), [
                'name'    => $this->owner->name,
                'email'   => $this->owner->email,
                'profile' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Profile field on the user should be set to the stored filename
        $this->assertNotNull($this->owner->fresh()->profile);
    }

    public function test_account_data_flashes_error_on_missing_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.account'), ['email' => 'x@example.com'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_account_data_flashes_error_on_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->owner)
            ->post(route('setting.account'), [
                'name'  => 'Owner',
                'email' => 'taken@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::passwordData ───────────────────────────────────────

    public function test_password_data_changes_password(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.password'), [
                'current_password' => 'secret123',
                'new_password'     => 'newpass456',
                'confirm_password' => 'newpass456',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpass456', $this->owner->fresh()->password));
    }

    public function test_password_data_flashes_error_on_wrong_current_password(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.password'), [
                'current_password' => 'wrongpassword',
                'new_password'     => 'newpass456',
                'confirm_password' => 'newpass456',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_password_data_flashes_error_when_confirm_does_not_match(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.password'), [
                'current_password' => 'secret123',
                'new_password'     => 'newpass456',
                'confirm_password' => 'different789',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::generalData ────────────────────────────────────────

    public function test_general_data_saves_application_name_for_owner(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.general'), [
                'application_name' => 'My Rentals',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'      => 'app_name',
            'value'     => 'My Rentals',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_general_data_uploads_logo_for_owner(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('logo.png')->mimeType('image/png');

        $this->actingAs($this->owner)
            ->post(route('setting.general'), [
                'application_name' => 'My Rentals',
                'logo'             => $logo,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $expectedFilename = $this->owner->id . '_logo.png';
        Storage::disk('public')->assertExists('upload/logo/' . $expectedFilename);
    }

    public function test_general_data_uploads_favicon_for_owner(): void
    {
        Storage::fake('public');

        $favicon = UploadedFile::fake()->image('favicon.png')->mimeType('image/png');

        $this->actingAs($this->owner)
            ->post(route('setting.general'), [
                'application_name' => 'My Rentals',
                'favicon'          => $favicon,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $expectedFilename = $this->owner->id . '_favicon.png';
        Storage::disk('public')->assertExists('upload/logo/' . $expectedFilename);
    }

    public function test_general_data_rejects_non_png_logo(): void
    {
        $logo = UploadedFile::fake()->image('logo.jpg')->mimeType('image/jpeg');

        $this->actingAs($this->owner)
            ->post(route('setting.general'), [
                'application_name' => 'My Rentals',
                'logo'             => $logo,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_general_data_uploads_home_images_for_owner(): void
    {
        Storage::fake('public');

        $img1 = UploadedFile::fake()->image('home1.png')->mimeType('image/png');
        $img2 = UploadedFile::fake()->image('home2.png')->mimeType('image/png');

        $this->actingAs($this->owner)
            ->post(route('setting.general'), [
                'application_name' => 'My Rentals',
                'image_home_1'     => $img1,
                'image_home_2'     => $img2,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('public')->assertExists('upload/home/' . $this->owner->id . '_image_home_1.png');
        Storage::disk('public')->assertExists('upload/home/' . $this->owner->id . '_image_home_2.png');
    }

    // ── SettingController::storeSignature ─────────────────────────────────────

    public function test_store_signature_saves_file_and_setting(): void
    {
        Storage::fake('public');

        $png = UploadedFile::fake()->image('signature.png')->mimeType('image/png');

        $this->actingAs($this->owner)
            ->post(route('AdminSignature.store'), ['signature' => $png])
            ->assertRedirect()
            ->assertSessionHas('success');

        // The DB setting should be created
        $this->assertDatabaseHas('settings', [
            'name'      => 'admin_signature',
            'parent_id' => 2, // hard-coded in controller
        ]);
    }

    public function test_store_signature_requires_auth(): void
    {
        $png = UploadedFile::fake()->image('sig.png');
        $this->post(route('AdminSignature.store'), ['signature' => $png])
            ->assertRedirect(route('login'));
    }

    public function test_store_signature_rejects_non_image(): void
    {
        $pdf = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->actingAs($this->owner)
            ->post(route('AdminSignature.store'), ['signature' => $pdf])
            ->assertSessionHasErrors(['signature'])
            ->assertStatus(302);
    }

    // ── SettingController::companyData ────────────────────────────────────────

    public function test_company_data_persists_settings_and_readable_via_helper(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.company'), [
                'company_name'    => 'Acme Rentals',
                'company_email'   => 'info@acme.com',
                'company_phone'   => '+212600000000',
                'company_address' => '1 Main St, Casablanca',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'      => 'company_name',
            'value'     => 'Acme Rentals',
            'parent_id' => $this->owner->id,
        ]);

        $settings = settings();
        $this->assertEquals('Acme Rentals', $settings['company_name']);
    }

    public function test_company_data_flashes_error_on_missing_company_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.company'), [
                'company_email'   => 'info@acme.com',
                'company_phone'   => '+212600000000',
                'company_address' => '1 Main St',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::smtpData ───────────────────────────────────────────

    public function test_smtp_data_persists_all_smtp_keys(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.smtp'), [
                'sender_name'       => 'Acme',
                'sender_email'      => 'noreply@acme.com',
                'server_driver'     => 'smtp',
                'server_host'       => 'smtp.acme.com',
                'server_port'       => '587',
                'server_username'   => 'user@acme.com',
                'server_password'   => 'smtppassword',
                'server_encryption' => 'tls',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'      => 'SERVER_HOST',
            'value'     => 'smtp.acme.com',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_smtp_data_flashes_error_on_missing_host(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.smtp'), [
                'sender_name'       => 'Acme',
                'sender_email'      => 'noreply@acme.com',
                'server_driver'     => 'smtp',
                'server_port'       => '587',
                'server_username'   => 'user',
                'server_password'   => 'pass',
                'server_encryption' => 'tls',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::siteSEOData ────────────────────────────────────────

    public function test_site_seo_data_persists_meta_fields(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.site.seo'), [
                'meta_seo_title'       => 'Best Car Rentals',
                'meta_seo_keyword'     => 'car, rental, cheap',
                'meta_seo_description' => 'Rent cars at the best price.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'meta_seo_title',
            'value' => 'Best Car Rentals',
        ]);
    }

    public function test_site_seo_data_uploads_meta_image(): void
    {
        Storage::fake();

        $image = UploadedFile::fake()->image('seo.jpg');

        $this->actingAs($this->owner)
            ->post(route('setting.site.seo'), [
                'meta_seo_title'       => 'Best Car Rentals',
                'meta_seo_keyword'     => 'car, rental',
                'meta_seo_description' => 'Rent cars.',
                'meta_seo_image'       => $image,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Controller stores the filename in settings and the file in upload/seo/
        $this->assertDatabaseHas('settings', [
            'name' => 'meta_seo_image',
            'type' => 'SEO',
        ]);
    }

    public function test_site_seo_data_flashes_error_on_missing_title(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.site.seo'), [
                'meta_seo_keyword'     => 'cars',
                'meta_seo_description' => 'desc',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::paymentData ────────────────────────────────────────

    public function test_payment_data_persists_currency(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'        => 'EUR',
                'CURRENCY_SYMBOL' => '€',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'      => 'CURRENCY',
            'value'     => 'EUR',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_payment_data_flashes_error_on_missing_currency(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), ['CURRENCY_SYMBOL' => '€'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── settings() cache ─────────────────────────────────────────────────────

    public function test_settings_result_is_cached_after_first_call(): void
    {
        $this->actingAs($this->owner);

        // Prime the cache
        settings();

        $cacheKey = 'settings_' . parentId();
        $this->assertTrue(Cache::has($cacheKey), 'settings() should store result in cache');
    }

    public function test_saving_general_settings_flushes_cache(): void
    {
        $this->actingAs($this->owner);

        // Prime the cache with a real settings() call, then save and verify it's cleared
        settings();
        $cacheKey = 'settings_' . parentId();
        $this->assertTrue(Cache::has($cacheKey));

        $this->actingAs($this->owner)
            ->post(route('setting.general'), ['application_name' => 'New App'])
            ->assertRedirect();

        $this->assertFalse(Cache::has($cacheKey), 'generalData should flush settings cache');
    }

    public function test_settings_queries_db_only_once_per_ttl(): void
    {
        $this->actingAs($this->owner);

        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            if (str_contains($query->sql, 'settings')) {
                $queryCount++;
            }
        });

        settings();
        settings(); // second call should hit cache, not DB

        $this->assertLessThanOrEqual(1, $queryCount, 'settings() should query DB at most once per TTL');
    }

    /** @dataProvider settingsFlushRouteProvider */
    public function test_each_settings_write_flushes_cache(string $routeName, array $payload): void
    {
        $this->actingAs($this->owner);
        settings();
        $cacheKey = 'settings_' . parentId();
        $this->assertTrue(Cache::has($cacheKey));

        $this->actingAs($this->owner)
            ->post(route($routeName), $payload)
            ->assertRedirect();

        $this->assertFalse(Cache::has($cacheKey), "{$routeName} should flush settings cache");
    }

    public static function settingsFlushRouteProvider(): array
    {
        return [
            'smtpData' => ['setting.smtp', [
                'sender_name'       => 'Test',
                'sender_email'      => 'test@example.com',
                'server_driver'     => 'smtp',
                'server_host'       => 'smtp.example.com',
                'server_port'       => '587',
                'server_username'   => 'user',
                'server_password'   => 'pass',
                'server_encryption' => 'tls',
            ]],
            'paymentData' => ['setting.payment', [
                'CURRENCY'        => 'EUR',
                'CURRENCY_SYMBOL' => '€',
            ]],
            'companyData' => ['setting.company', [
                'company_name'    => 'Test Co',
                'company_email'   => 'info@test.com',
                'company_phone'   => '+1234567890',
                'company_address' => '1 Main St',
            ]],
            'themeSettings' => ['theme.settings', []],
            'siteSEOData' => ['setting.site.seo', [
                'meta_seo_title'       => 'Title',
                'meta_seo_keyword'     => 'kw',
                'meta_seo_description' => 'desc',
            ]],
            'googleRecaptchaData' => ['setting.google.recaptcha', [
                'recaptcha_key'    => 'site-key',
                'recaptcha_secret' => 'secret-key',
            ]],
        ];
    }

    // ── SettingController::googleRecaptchaData ────────────────────────────────

    public function test_recaptcha_data_persists_keys(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.google.recaptcha'), [
                'recaptcha_key'    => 'site-key-abc',
                'recaptcha_secret' => 'secret-key-xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'recaptcha_key',
            'value' => 'site-key-abc',
            'type'  => 'recaptcha',
        ]);
    }

    public function test_recaptcha_data_flashes_error_on_missing_key(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.google.recaptcha'), [
                'recaptcha_secret' => 'secret-key-xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::account (GET) ──────────────────────────────────────

    public function test_account_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.account'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Account')
                ->has('loginUser.id')
                ->has('loginUser.name')
            );
    }

    // ── SettingController::password (GET) ─────────────────────────────────────

    public function test_password_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.password'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Password')
                ->has('loginUser.id')
            );
    }

    // ── SettingController::company (GET) ──────────────────────────────────────

    public function test_company_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.company'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Company')
                ->has('settings')
            );
    }

    // ── SettingController::general (GET) ──────────────────────────────────────

    public function test_general_page_requires_auth(): void
    {
        $this->get(route('setting.general'))->assertRedirect(route('login'));
    }

    public function test_general_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.general'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/General')
                ->has('settings')
            );
    }

    // ── SettingController::smtp (GET) ─────────────────────────────────────────

    public function test_smtp_page_requires_auth(): void
    {
        $this->get(route('setting.smtp'))->assertRedirect(route('login'));
    }

    public function test_smtp_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.smtp'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Smtp')
                ->has('settings')
            );
    }

    // ── SettingController::payment (GET) ──────────────────────────────────────

    public function test_payment_page_requires_auth(): void
    {
        $this->get(route('setting.payment'))->assertRedirect(route('login'));
    }

    public function test_payment_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.payment'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Payment')
                ->has('settings')
            );
    }

    // ── SettingController::googleRecaptcha (GET) ──────────────────────────────

    public function test_recaptcha_page_requires_auth(): void
    {
        $this->get(route('setting.google.recaptcha'))->assertRedirect(route('login'));
    }

    public function test_recaptcha_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.google.recaptcha'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Recaptcha')
                ->has('settings')
            );
    }

    // ── SettingController::siteSEO (GET) ──────────────────────────────────────

    public function test_site_seo_page_requires_auth(): void
    {
        $this->get(route('setting.site.seo'))->assertRedirect(route('login'));
    }

    public function test_site_seo_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.site.seo'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/SiteSeo')
                ->has('settings')
            );
    }

    // ── SettingController::accountDelete ──────────────────────────────────────

    public function test_account_delete_requires_auth(): void
    {
        $this->delete(route('setting.account.delete'))->assertRedirect(route('login'));
    }

    public function test_account_delete_removes_authenticated_user(): void
    {
        $userToDelete = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($userToDelete)
            ->delete(route('setting.account.delete'))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    // ── SettingController::languageChange ─────────────────────────────────────

    public function test_language_change_updates_user_lang(): void
    {
        $this->actingAs($this->owner)
            ->get(route('language.change', 'fr'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $this->owner->id, 'lang' => 'fr']);
    }

    // ── SettingController::paymentData — bank transfer branch ─────────────────

    public function test_payment_data_with_bank_transfer_persists_bank_settings(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'            => 'MAD',
                'CURRENCY_SYMBOL'     => 'DH',
                'bank_transfer_payment' => 'on',
                'bank_name'           => 'CIH Bank',
                'bank_holder_name'    => 'Acme Rentals',
                'bank_account_number' => '123456789',
                'bank_ifsc_code'      => 'CIHM0001',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'bank_name',
            'value' => 'CIH Bank',
            'type'  => 'payment',
        ]);
    }

    // ── SettingController::paymentData — bank transfer validation fail ─────────

    public function test_payment_data_bank_transfer_flashes_error_on_missing_bank_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'              => 'MAD',
                'CURRENCY_SYMBOL'       => 'DH',
                'bank_transfer_payment' => 'on',
                // bank_name missing
                'bank_holder_name'      => 'Acme',
                'bank_account_number'   => '123',
                'bank_ifsc_code'        => 'X001',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::paymentData — stripe branch ────────────────────────

    public function test_payment_data_with_stripe_persists_stripe_settings(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'        => 'EUR',
                'CURRENCY_SYMBOL' => '€',
                'stripe_payment'  => 'on',
                'stripe_key'      => 'pk_test_abc',
                'stripe_secret'   => 'sk_test_xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'STRIPE_KEY',
            'value' => 'pk_test_abc',
            'type'  => 'payment',
        ]);
    }

    public function test_payment_data_stripe_flashes_error_on_missing_stripe_key(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'        => 'EUR',
                'CURRENCY_SYMBOL' => '€',
                'stripe_payment'  => 'on',
                // stripe_key missing
                'stripe_secret'   => 'sk_test_xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::paymentData — paypal branch ────────────────────────

    public function test_payment_data_with_paypal_persists_paypal_settings(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'          => 'USD',
                'CURRENCY_SYMBOL'   => '$',
                'paypal_payment'    => 'on',
                'paypal_mode'       => 'sandbox',
                'paypal_client_id'  => 'client-id-abc',
                'paypal_secret_key' => 'secret-key-xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'paypal_client_id',
            'value' => 'client-id-abc',
            'type'  => 'payment',
        ]);
    }

    public function test_payment_data_paypal_flashes_error_on_missing_paypal_mode(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'          => 'USD',
                'CURRENCY_SYMBOL'   => '$',
                'paypal_payment'    => 'on',
                // paypal_mode missing
                'paypal_client_id'  => 'client-id',
                'paypal_secret_key' => 'secret',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::paymentData — flutterwave branch ───────────────────

    public function test_payment_data_with_flutterwave_persists_flutterwave_settings(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'                => 'NGN',
                'CURRENCY_SYMBOL'         => '₦',
                'flutterwave_payment'     => 'on',
                'flutterwave_public_key'  => 'FLWPUBK_test_abc',
                'flutterwave_secret_key'  => 'FLWSECK_test_xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'flutterwave_public_key',
            'value' => 'FLWPUBK_test_abc',
            'type'  => 'payment',
        ]);
    }

    public function test_payment_data_flutterwave_flashes_error_on_missing_public_key(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.payment'), [
                'CURRENCY'                => 'NGN',
                'CURRENCY_SYMBOL'         => '₦',
                'flutterwave_payment'     => 'on',
                // flutterwave_public_key missing
                'flutterwave_secret_key'  => 'FLWSECK_test_xyz',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::generalData — super admin path ────────────────────

    public function test_general_data_saves_application_name_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [
                'application_name' => 'Super App Name',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_general_data_rejects_non_png_logo_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $logo = UploadedFile::fake()->image('logo.jpg')->mimeType('image/jpeg');

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [
                'application_name' => 'Super App',
                'logo'             => $logo,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_general_data_uploads_logo_for_super_admin(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $logo = UploadedFile::fake()->image('logo.png')->mimeType('image/png');

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [
                'application_name' => 'Super App',
                'logo'             => $logo,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('public')->assertExists('upload/logo/logo.png');
    }

    public function test_general_data_uploads_favicon_for_super_admin(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $favicon = UploadedFile::fake()->image('fav.png')->mimeType('image/png');

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [
                'application_name' => 'Super App',
                'favicon'          => $favicon,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('public')->assertExists('upload/logo/favicon.png');
    }

    public function test_general_data_uploads_home_images_for_super_admin(): void
    {
        Storage::fake('public');

        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $img1 = UploadedFile::fake()->image('home1.png')->mimeType('image/png');
        $img2 = UploadedFile::fake()->image('home2.png')->mimeType('image/png');

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [
                'application_name' => 'Super App',
                'image_home_1'     => $img1,
                'image_home_2'     => $img2,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('public')->assertExists('upload/home/image_home_1.png');
        Storage::disk('public')->assertExists('upload/home/image_home_2.png');
    }

    public function test_general_data_rejects_missing_application_name_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('setting.general'), [])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_general_data_denies_non_owner_non_super_admin(): void
    {
        $employee = User::factory()->create([
            'type'      => 'employee',
            'parent_id' => $this->owner->id,
        ]);

        $this->actingAs($employee)
            ->post(route('setting.general'), ['application_name' => 'Test'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── SettingController::themeSettings ─────────────────────────────────────

    public function test_theme_settings_persists_values_for_owner(): void
    {
        $this->actingAs($this->owner)
            ->post(route('theme.settings'), [
                'dark_mode' => 'on',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_theme_settings_persists_landing_page_toggle_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('theme.settings'), [
                'landing_page'             => 'on',
                'register_page'            => 'on',
                'owner_email_verification' => 'on',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'  => 'landing_page',
            'value' => 'on',
            'type'  => 'common',
        ]);
    }

    public function test_theme_settings_defaults_landing_page_to_off_when_not_set_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);

        // Submit without landing_page, register_page, or owner_email_verification
        $this->actingAs($superAdmin)
            ->post(route('theme.settings'), [])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    // ── SettingController::branding (GET) ─────────────────────────────────────

    public function test_branding_page_requires_auth(): void
    {
        $this->get(route('setting.branding'))->assertRedirect(route('login'));
    }

    public function test_branding_page_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.branding'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Settings/Branding')
                ->has('settings')
            );
    }

    // ── SettingController::brandingData (POST) ────────────────────────────────

    public function test_branding_data_saves_brand_color_for_owner(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.branding'), ['brand_color' => '#a13a00'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'name'      => 'brand_color',
            'value'     => '#a13a00',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_branding_data_saves_all_fields(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.branding'), [
                'brand_color'   => '#e85c13',
                'accent_color'  => '#10b981',
                'brand_neutral' => 'warm',
                'layout_mode'   => 'darkmode',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        foreach (['brand_color' => '#e85c13', 'accent_color' => '#10b981', 'brand_neutral' => 'warm', 'layout_mode' => 'darkmode'] as $name => $value) {
            $this->assertDatabaseHas('settings', ['name' => $name, 'value' => $value, 'parent_id' => $this->owner->id]);
        }
    }

    public function test_branding_data_rejects_invalid_hex(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.branding'), ['brand_color' => 'notahex'])
            ->assertSessionHasErrors('brand_color');
    }

    public function test_branding_data_rejects_invalid_neutral(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.branding'), ['brand_neutral' => 'hot'])
            ->assertSessionHasErrors('brand_neutral');
    }

    public function test_branding_data_allows_empty_brand_color(): void
    {
        $this->actingAs($this->owner)
            ->post(route('setting.branding'), ['brand_color' => null])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_branding_page_denies_unauthenticated_employee(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->get(route('setting.branding'))
            ->assertForbidden();
    }

    public function test_branding_data_denies_unauthenticated_employee(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->post(route('setting.branding'), ['brand_color' => '#a13a00'])
            ->assertForbidden();
    }
}
