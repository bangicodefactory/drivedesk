<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * /contact (BAN-333).
 *
 * The page it replaces was `Route::view('/contact', 'client.pages.contact')`,
 * whose entire body was "This is a placeholder contact page. Replace with real
 * content." — linked from the storefront header, the footer and the booking
 * confirmation. (It is not in sitemap.xml; SeoController excludes it, for
 * reasons this change removes.)
 *
 * The tests that matter most here are the ones about *not* accepting a message
 * it cannot deliver. The sibling POST /newsletter/subscribe still reports
 * success and throws the address away; that is the failure this controller was
 * written to avoid, and a status check cannot see it.
 *
 * public_storefront is forced rather than inherited from the client (CLAUDE.md
 * §10.2 rule 6).
 */
class ContactControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('drivedesk');
        config(['client.features.public_storefront' => true]);
    }

    /** A guest's settings bucket is parent_id = 1 (see the settings() helper). */
    private function setContactEmail(?string $email): void
    {
        if ($email !== null) {
            Setting::create(['name' => 'company_email', 'value' => $email, 'parent_id' => 1]);
        }

        flushSettingsCache(1);
    }

    public function test_contact_renders_a_real_page(): void
    {
        $this->setContactEmail('agence@example.com');

        $this->get(route('contact'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Contact'))
            ->assertDontSee('placeholder contact page');
    }

    public function test_the_form_is_offered_when_there_is_somewhere_to_deliver(): void
    {
        $this->setContactEmail('agence@example.com');

        $this->get(route('contact'))->assertInertia(fn (Assert $page) => $page
            ->where('canSendMessage', true));
    }

    /**
     * company_email is a Setting an owner fills in. Until they do, there is no
     * recipient — so the page must not offer a form at all.
     */
    public function test_the_form_is_withheld_when_no_address_is_configured(): void
    {
        $this->setContactEmail(null);

        $this->get(route('contact'))->assertInertia(fn (Assert $page) => $page
            ->where('canSendMessage', false));
    }

    public function test_a_setting_that_is_not_an_email_does_not_count_as_a_recipient(): void
    {
        $this->setContactEmail('not-an-address');

        $this->get(route('contact'))->assertInertia(fn (Assert $page) => $page
            ->where('canSendMessage', false));
    }

    public function test_a_message_is_delivered_to_the_configured_address(): void
    {
        Mail::fake();
        $this->setContactEmail('agence@example.com');

        $this->post(route('contact.send'), [
            'name'      => 'Yassine Berrada',
            'email'     => 'yassine@example.com',
            'phone'     => '+212661223344',
            'reference' => 'BR-00001',
            'message'   => 'Bonjour, je voudrais prolonger ma location de deux jours.',
        ])->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo('agence@example.com')
                && $mail->data['reference'] === 'BR-00001'
                && $mail->data['message'] === 'Bonjour, je voudrais prolonger ma location de deux jours.';
        });
    }

    /** The reference is a hint for whoever reads the message, not a required field. */
    public function test_a_message_without_a_reference_is_still_delivered(): void
    {
        Mail::fake();
        $this->setContactEmail('agence@example.com');

        $this->post(route('contact.send'), [
            'name'    => 'Yassine Berrada',
            'email'   => 'yassine@example.com',
            'message' => 'Quels sont vos tarifs à la semaine ?',
        ])->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(ContactMessage::class, fn (ContactMessage $mail) => $mail->data['reference'] === null);
    }

    /**
     * The whole point of the controller. With nowhere to deliver, the visitor
     * must be told — never thanked for a message that went nowhere.
     */
    public function test_it_refuses_rather_than_silently_dropping_a_message(): void
    {
        Mail::fake();
        $this->setContactEmail(null);

        $this->post(route('contact.send'), [
            'name'    => 'Yassine Berrada',
            'email'   => 'yassine@example.com',
            'message' => 'Bonjour.',
        ])->assertRedirect()->assertSessionHas('error')->assertSessionMissing('success');

        Mail::assertNothingSent();
    }

    /**
     * SMTP credentials are per-tenant settings an owner fills in, so a wrong
     * password is a configuration mistake rather than an exceptional one. An
     * uncaught TransportException would be a 500 on a public URL -- exactly
     * the failure BAN-329 was about -- and it would also tell the visitor
     * nothing about whether their message arrived.
     */
    public function test_a_failing_mailer_is_reported_not_a_500(): void
    {
        $this->setContactEmail('agence@example.com');

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp is down'));

        $response = $this->post(route('contact.send'), [
            'name'    => 'Yassine Berrada',
            'email'   => 'yassine@example.com',
            'message' => 'Bonjour.',
        ]);

        $this->assertLessThan(400, $response->getStatusCode());
        $response->assertSessionHas('error')->assertSessionMissing('success');
    }

    public function test_it_validates_before_sending(): void
    {
        Mail::fake();
        $this->setContactEmail('agence@example.com');

        $this->post(route('contact.send'), [
            'name'    => '',
            'email'   => 'not-an-email',
            'message' => '',
        ])->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    /** Unauthenticated, and it sends mail — throttled like its two siblings. */
    public function test_the_form_is_rate_limited(): void
    {
        Mail::fake();
        $this->setContactEmail('agence@example.com');

        $payload = [
            'name'    => 'Yassine Berrada',
            'email'   => 'yassine@example.com',
            'message' => 'Bonjour.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.send'), $payload)->assertRedirect();
        }

        $this->post(route('contact.send'), $payload)->assertStatus(429);
    }

    /**
     * Mail::fake() never builds the view, so every test above would still pass
     * with a broken Blade template -- the failure would only appear in
     * production, on a form a visitor just submitted. Render it for real.
     */
    public function test_the_email_template_renders(): void
    {
        $html = (new ContactMessage([
            'name'      => 'Yassine Berrada',
            'email'     => 'yassine@example.com',
            'phone'     => null,
            'reference' => 'BR-00001',
            'message'   => 'Bonjour.',
        ]))->render();

        $this->assertStringContainsString('Yassine Berrada', $html);
        $this->assertStringContainsString('BR-00001', $html);
        $this->assertStringContainsString('Bonjour.', $html);
    }

    /** A missing phone renders as a dash rather than blowing up on null. */
    public function test_the_email_template_renders_without_the_optional_fields(): void
    {
        $html = (new ContactMessage([
            'name'      => 'Yassine Berrada',
            'email'     => 'yassine@example.com',
            'phone'     => null,
            'reference' => null,
            'message'   => 'Bonjour.',
        ]))->render();

        $this->assertStringContainsString('Yassine Berrada', $html);
    }

    /**
     * Every user-visible string this controller and its mailable produce goes
     * through __() with an English sentence as the key -- the convention
     * DemoRequestController already uses. The first version of this PR added
     * 95 storefront keys per locale and forgot its own PHP ones, so a French
     * visitor got an English toast and the agency's notification arrived
     * half-translated. drivedesk's public default locale is French.
     */
    public function test_its_own_strings_are_translated_into_the_locales_this_client_serves(): void
    {
        $strings = [
            'Thanks — your message has been sent. We will reply shortly.',
            'Sending is unavailable right now. Please call or message us instead.',
            'Message from :name',
            'Message from :name — booking :reference',
            'New message from the website',
            'Booking reference',
            'Reply to :name',
        ];

        foreach (config('client.supported_locales') as $locale) {
            $catalogue = json_decode(file_get_contents(base_path("resources/lang/{$locale}.json")), true);

            foreach ($strings as $string) {
                $this->assertArrayHasKey(
                    $string,
                    $catalogue,
                    "resources/lang/{$locale}.json is missing \"{$string}\""
                );
            }
        }
    }

    /** The subject line is the half a French recipient sees first. */
    public function test_the_email_subject_is_translated(): void
    {
        app()->setLocale('fr');

        $mail = new ContactMessage([
            'name'      => 'Yassine Berrada',
            'email'     => 'yassine@example.com',
            'phone'     => null,
            'reference' => 'BR-00001',
            'message'   => 'Bonjour.',
        ]);
        $mail->build();

        $this->assertSame('Message de Yassine Berrada — réservation BR-00001', $mail->subject);
    }

    /** Both verbs live behind feature:public_storefront, so both disappear together. */
    public function test_both_verbs_404_when_the_storefront_is_off(): void
    {
        config(['client.features.public_storefront' => false]);

        $this->get('/contact')->assertNotFound();
        $this->post('/contact', [
            'name'    => 'Yassine Berrada',
            'email'   => 'yassine@example.com',
            'message' => 'Bonjour.',
        ])->assertNotFound();
    }
}
