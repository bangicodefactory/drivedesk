<?php

namespace Tests\Feature;

use App\Mail\DemoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class DemoGatewayTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

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
        $this->asClient('directonderweg');

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
        $this->asClient('directonderweg');

        // The route exists but the feature:demo_gateway middleware blocks it.
        $this->post(route('demo.request'), $this->valid)->assertNotFound();

        Mail::assertNothingSent();
    }
}
