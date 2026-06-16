<?php

namespace Tests\Feature;

use App\Mail\DemoCredentials;
use App\Mail\DemoRequest;
use App\Models\User;
use Database\Seeders\DefaultDataUsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Demo request → approval flow (BAN-249). A pending request is an inactive
 * `manager` sub-user of the demo tenant (no demo_requests table — schema is
 * frozen, §4); approval activates + verifies it and emails a set-password link.
 */
class DemoApprovalTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private User $owner;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // System super-admin + the demo tenant owner (with its manager role).
        $this->seed(DefaultDataUsersTableSeeder::class);
        $this->superAdmin = User::where('type', 'super admin')->firstOrFail();
        $this->owner = User::where('type', 'owner')->firstOrFail();
    }

    private array $valid = [
        'name'    => 'Sara Idrissi',
        'company' => 'Atlas Cars',
        'email'   => 'sara@atlascars.ma',
        'phone'   => '+212 600000000',
        'message' => 'We run ~40 vehicles.',
    ];

    private function makePending(string $email = 'pending@atlascars.ma'): User
    {
        $user = User::create([
            'name'         => 'Pending Prospect',
            'email'        => $email,
            'password'     => Hash::make(Str::random(40)),
            'type'         => 'manager',
            'parent_id'    => $this->owner->id,
            'company_name' => 'Atlas Cars',
            'profile'      => 'avatar.png',
            'lang'         => 'english',
            'is_active'    => 0,
        ]);

        $role = Role::where('name', 'manager')->where('parent_id', $this->owner->id)->first();
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    // ── index (super-admin listing) ──────────────────────────────────────────

    public function test_super_admin_sees_only_pending_demo_rows_on_the_index(): void
    {
        $this->asClient('drivedesk');
        $this->makePending('a@atlascars.ma');
        $this->makePending('b@atlascars.ma');

        $this->actingAs($this->superAdmin)
            ->get(route('demo-requests.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('DemoRequests/Index')
                ->has('requests', 2)
                ->has('requests.0', fn (Assert $r) => $r
                    ->hasAll(['id', 'name', 'email', 'company', 'phone', 'created_at'])
                )
            );
    }

    public function test_index_is_forbidden_for_non_super_admin(): void
    {
        $this->asClient('drivedesk');

        $this->actingAs($this->owner)
            ->get(route('demo-requests.index'))
            ->assertForbidden();
    }

    public function test_index_is_404_on_a_non_demo_client(): void
    {
        $this->asClient('directonderweg');

        $this->actingAs($this->superAdmin)
            ->get(route('demo-requests.index'))
            ->assertNotFound();
    }

    // ── store provisions a pending user ───────────────────────────────────────

    public function test_demo_request_provisions_a_pending_inactive_manager(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');

        $this->post(route('demo.request'), $this->valid)
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(DemoRequest::class);

        $user = User::where('email', $this->valid['email'])->first();
        $this->assertNotNull($user);
        $this->assertSame('manager', $user->type);
        $this->assertSame($this->owner->id, $user->parent_id);
        $this->assertSame(0, (int) $user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Atlas Cars', $user->company_name);
        $this->assertTrue($user->hasRole('manager'));
    }

    public function test_demo_request_is_idempotent_on_duplicate_email(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');

        $this->post(route('demo.request'), $this->valid);
        $this->post(route('demo.request'), $this->valid);

        $this->assertSame(1, User::where('email', $this->valid['email'])->count());
    }

    // ── approve ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_approve_a_pending_demo(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');
        $pending = $this->makePending();

        $this->actingAs($this->superAdmin)
            ->post(route('demo-requests.approve', $pending))
            ->assertRedirect()
            ->assertSessionHas('success');

        $pending->refresh();
        $this->assertSame(1, (int) $pending->is_active);
        $this->assertNotNull($pending->email_verified_at);

        Mail::assertSent(DemoCredentials::class, function (DemoCredentials $mail) use ($pending) {
            return $mail->hasTo($pending->email)
                && $mail->user->is($pending)
                && str_contains($mail->url, 'reset-password');
        });
    }

    // ── decline ───────────────────────────────────────────────────────────────

    public function test_super_admin_can_decline_a_pending_demo(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');
        $pending = $this->makePending();

        $this->actingAs($this->superAdmin)
            ->post(route('demo-requests.decline', $pending))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $pending->id]);
        Mail::assertNothingSent();
    }

    // ── gating ────────────────────────────────────────────────────────────────

    public function test_non_super_admin_cannot_approve(): void
    {
        Mail::fake();
        $this->asClient('drivedesk');
        $pending = $this->makePending();

        // The tenant owner is authenticated but is not a super-admin.
        $this->actingAs($this->owner)
            ->post(route('demo-requests.approve', $pending))
            ->assertForbidden();

        $pending->refresh();
        $this->assertSame(0, (int) $pending->is_active);
        Mail::assertNothingSent();
    }

    public function test_approve_404s_for_a_user_that_is_not_a_pending_demo(): void
    {
        $this->asClient('drivedesk');

        // The owner is an active non-manager — not a pending demo row.
        $this->actingAs($this->superAdmin)
            ->post(route('demo-requests.approve', $this->owner))
            ->assertNotFound();
    }

    public function test_demo_approval_routes_are_404_on_a_non_demo_client(): void
    {
        $this->asClient('directonderweg'); // demo_gateway off
        $pending = $this->makePending();

        $this->actingAs($this->superAdmin)
            ->post(route('demo-requests.approve', $pending))
            ->assertNotFound();

        $this->actingAs($this->superAdmin)
            ->post(route('demo-requests.decline', $pending))
            ->assertNotFound();
    }
}
