<?php

namespace Tests\Feature;

use App\Mail\DemoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\AsInstalledApp;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class DemoGatewayTest extends TestCase
{
    use AsInstalledApp;
    use RefreshDatabase;
    use WithClient;

    protected function setUp(): void
    {
        parent::setUp();

        // See Tests\Concerns\AsInstalledApp — a fresh CI checkout has no
        // storage/installed marker, so a guest GET / would 302 to the installer.
        $this->markAppInstalled();
    }

    protected function tearDown(): void
    {
        $this->removeInstalledMarkerIfCreated();

        parent::tearDown();
    }

    private array $valid = [
        'name'    => 'Sara Idrissi',
        'company' => 'Atlas Cars',
        'email'   => 'sara@atlascars.ma',
        'phone'   => '+212 600000000',
        'message' => 'We run ~40 vehicles and want to switch.',
    ];

    // ── landing visibility ────────────────────────────────────────────────────

    public function test_guest_home_renders_marketing_landing_when_gateway_on(): void
    {
        $this->asClient('drivedesk');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/DemoGateway'));
    }

    public function test_guest_home_redirects_to_login_when_gateway_off(): void
    {
        $this->asClient('acme');
        // acme keeps the B2C storefront on by default, and / now serves that
        // storefront's home when public_storefront is on and demo_gateway is
        // off (see PublicStorefrontTest::test_root_serves_the_storefront_home_
        // for_clients_that_keep_it). This test is about the "neither public
        // face is on" case specifically, so pin that flag off rather than lean
        // on the fixture's default (CLAUDE.md §10.2 rule 6).
        config(['client.features.public_storefront' => false]);

        $this->get('/')->assertRedirect(route('login'));
    }

    // ── demo request endpoint ─────────────────────────────────────────────────

    public function test_demo_request_sends_mail_to_product_inbox(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');

        $this->post(route('demo.request'), $this->valid)
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(DemoRequest::class, function (DemoRequest $mail) {
            return $mail->hasTo('admin@bangicode.ma')
                && $mail->data['company'] === 'Atlas Cars';
        });
    }

    public function test_demo_request_validates_required_fields(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');

        $this->post(route('demo.request'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'company', 'email']);

        Mail::assertNothingSent();
    }

    public function test_demo_request_is_404_when_gateway_off(): void
    {
        Mail::fake();
        $this->asClient('acme');

        // The route exists but the feature:demo_gateway middleware blocks it.
        $this->post(route('demo.request'), $this->valid)->assertNotFound();

        Mail::assertNothingSent();
    }

    public function test_demo_request_is_rate_limited(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');

        // throttle:5,1 — five succeed, the sixth from the same IP is throttled.
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('demo.request'), $this->valid)->assertRedirect();
        }

        $this->post(route('demo.request'), $this->valid)->assertStatus(429);
    }
}
