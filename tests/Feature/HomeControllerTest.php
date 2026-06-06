<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        Permission::firstOrCreate(['name' => 'manage booking', 'guard_name' => 'web']);
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

    // ── HomeController::index — super admin ───────────────────────────────────

    public function test_dashboard_returns_200_for_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    // ── Inertia path ──────────────────────────────────────────────────────────

    public function test_inertia_dashboard_renders_correct_component_for_owner(): void
    {

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

    // ── Operational widgets (Stitch-aligned dashboard) ───────────────────────

    public function test_inertia_dashboard_owner_has_operational_widgets(): void
    {
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('operational.carsOut')
                ->has('operational.totalVehicles')
                ->has('operational.returnsDueToday')
                ->has('operational.overdue')
                ->has('operational.maintenanceDue')
                ->has('operational.revenueToday')
                ->has('operational.revenueMonth')
                ->has('immediateActions')
                ->has('fleetAvailability.days', 7)
                ->has('fleetAvailability.vehicles')
            );
    }

    public function test_operational_cars_out_counts_active_booking_today(): void
    {
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        Booking::factory()->create([
            'parent_id'  => $owner->id,
            'start_date' => now()->subDay(),
            'end_date'   => now()->addDay(),
            'status'     => 'in_progress',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('operational.carsOut', fn ($v) => $v >= 1));
    }

    public function test_immediate_actions_and_fleet_hidden_without_permissions(): void
    {
        // Owner with data but no manage-booking/reminder sees no row-level lists.
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        \App\Models\Vehicle::factory()->create(['parent_id' => $owner->id]);
        Booking::factory()->create([
            'parent_id'  => $owner->id,
            'start_date' => now()->subDays(3),
            'end_date'   => now()->subDay(),     // overdue
            'status'     => 'in_progress',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('immediateActions', [])
                ->where('fleetAvailability.vehicles', [])
                // aggregate counts stay visible
                ->where('operational.overdue', fn ($v) => $v >= 1));
    }

    public function test_fleet_visible_with_manage_booking(): void
    {
        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $owner->givePermissionTo('manage booking');
        \App\Models\Vehicle::factory()->create(['parent_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('fleetAvailability.vehicles', 1));
    }

    public function test_inertia_dashboard_owner_without_manage_reminder_has_empty_reminders(): void
    {
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

    // ── GROUP BY query optimisation (BAN-235) ─────────────────────────────────

    public function test_organization_by_month_returns_12_labels_and_data_points(): void
    {
$superAdmin = User::factory()->superAdmin()->create(['parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('organizationByMonth.label', fn ($v) => count($v) === 12)
                ->where('organizationByMonth.data',  fn ($v) => count($v) === 12)
            );
    }

    public function test_income_expense_by_month_returns_12_labels_and_series(): void
    {
$owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('incomeExpenseByMonth.label',   fn ($v) => count($v) === 12)
                ->where('incomeExpenseByMonth.income',  fn ($v) => count($v) === 12)
                ->where('incomeExpenseByMonth.expense', fn ($v) => count($v) === 12)
            );
    }

    public function test_income_expense_by_month_sums_are_correct(): void
    {
$owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        Booking::factory()->create(['parent_id' => $owner->id, 'amount' => 500, 'start_date' => now()->startOfMonth()]);
        Expense::factory()->create(['parent_id' => $owner->id, 'amount' => 200, 'date' => now()->startOfMonth()]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('incomeExpenseByMonth.income',  fn ($v) => (float) $v[now()->month - 1] >= 500)
                ->where('incomeExpenseByMonth.expense', fn ($v) => (float) $v[now()->month - 1] >= 200)
            );
    }

    public function test_dashboard_fires_at_most_3_monthly_queries(): void
    {
$owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $monthlyQueries = 0;
        DB::listen(function ($query) use (&$monthlyQueries) {
            if (preg_match('/GROUP BY|groupby/i', $query->sql)) {
                $monthlyQueries++;
            }
        });

        $this->actingAs($owner)->get(route('dashboard'));

        $this->assertLessThanOrEqual(3, $monthlyQueries,
            "Dashboard should fire at most 3 GROUP BY queries (was {$monthlyQueries})"
        );
    }
}
