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

    private bool $createdInstalledMarker = false;

    protected function setUp(): void
    {
        parent::setUp();

        // HomeController@index's guest branch runs `if (!file_exists(setup()))
        // { header('location:install'); die; }` before anything else — the
        // legacy rachidlaasri installer guard. `setup()` is storage/installed,
        // which a fresh CI checkout does not have, so a guest GET / hits `die`
        // and kills the PHPUnit process mid-run — *before* it writes the
        // --coverage-php report, which surfaces as "Unable to get coverage using
        // Xdebug" → 0% → the --min gate fails. These are the first tests to
        // exercise the guest / branch, so they must put the app in its normal
        // installed state. Production/CI always run installed; this just matches
        // that. Created here, removed in tearDown so we never leave a stray
        // marker on a dev box that wasn't installed.
        $marker = setup();
        if (! file_exists($marker)) {
            @mkdir(dirname($marker), 0755, true);
            file_put_contents($marker, '');
            $this->createdInstalledMarker = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdInstalledMarker && file_exists(setup())) {
            @unlink(setup());
        }

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
