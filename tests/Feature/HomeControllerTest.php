<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        Permission::firstOrCreate(['name' => 'manage reminder', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // ── HomeController::index — owner ─────────────────────────────────────────

    public function test_dashboard_returns_200_for_owner(): void
    {
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $owner->givePermissionTo('manage reminder');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_dashboard_contains_booking_and_driver_totals_for_owner(): void
    {
        config(['app.inertia_enabled' => false]);
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        User::factory()->create(['type' => 'driver', 'parent_id' => $owner->id]);
        Booking::factory()->create(['parent_id' => $owner->id, 'amount' => 150]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('result', function ($result) {
            return $result['totalDriver'] >= 1 && $result['totalBooking'] >= 1;
        });
    }

    public function test_dashboard_owner_without_manage_reminder_gets_empty_reminders(): void
    {
        config(['app.inertia_enabled' => false]);
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('reminders', function ($reminders) {
            return $reminders->isEmpty();
        });
    }

    // ── HomeController::index — super admin ───────────────────────────────────

    public function test_dashboard_returns_200_for_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_super_admin_dashboard_contains_organization_totals(): void
    {
        config(['app.inertia_enabled' => false]);
        $superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);

        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('result', function ($result) {
            return $result['totalOrganization'] >= 2;
        });
    }

    // ── Inertia path (INERTIA_ENABLED=true) ───────────────────────────────────

    public function test_inertia_dashboard_renders_correct_component_for_owner(): void
    {
        config(['app.inertia_enabled' => true]);

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('stats')
                ->has('reminders')
                ->has('incomeExpenseByMonth')
            );
    }

    public function test_inertia_dashboard_owner_stats_contain_correct_counts(): void
    {
        config(['app.inertia_enabled' => true]);

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        User::factory()->create(['type' => 'driver', 'parent_id' => $owner->id]);
        Booking::factory()->create(['parent_id' => $owner->id, 'amount' => 250]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.totalDriver', fn ($v) => $v >= 1)
                ->where('stats.totalBooking', fn ($v) => $v >= 1)
                ->where('stats.totalIncome', fn ($v) => $v >= 250)
            );
    }

    public function test_inertia_dashboard_owner_without_manage_reminder_has_empty_reminders(): void
    {
        config(['app.inertia_enabled' => true]);

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('reminders', [])
            );
    }

    public function test_inertia_dashboard_renders_correct_component_for_super_admin(): void
    {
        config(['app.inertia_enabled' => true]);

        $superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('stats')
                ->has('stats.totalOrganization')
                ->has('organizationByMonth')
            );
    }

    public function test_inertia_dashboard_reminders_carry_vehicle_status_and_note(): void
    {
        config(['app.inertia_enabled' => true]);

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $owner->givePermissionTo('manage reminder');

        $vehicle = \App\Models\Vehicle::factory()->create([
            'parent_id'     => $owner->id,
            'name'          => 'BMW X5',
            'license_plate' => 'XYZ-123',
        ]);

        \App\Models\Reminder::factory()->create([
            'parent_id'     => $owner->id,
            'id_vehicle'    => $vehicle->id,
            'reminder_date' => now()->addDays(3),
            'note'          => 'Oil change due',
            'status'        => 'urgent',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('reminders.0.note',   'Oil change due')
                ->where('reminders.0.status', 'urgent')
                ->where('reminders.0.vehicle.name',          'BMW X5')
                ->where('reminders.0.vehicle.license_plate', 'XYZ-123')
            );
    }

    public function test_inertia_super_admin_dashboard_stats_contain_correct_org_count(): void
    {
        config(['app.inertia_enabled' => true]);

        $superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);
        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.totalOrganization', fn ($v) => $v >= 2)
            );
    }
}
