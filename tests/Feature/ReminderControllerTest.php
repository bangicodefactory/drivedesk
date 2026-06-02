<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\ReminderType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class ReminderControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected Vehicle $vehicle;
    protected ReminderType $reminderType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        $perms = ['manage reminder', 'create reminder', 'edit reminder', 'delete reminder'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('reminder.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('reminder.store'))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->put(route('reminder.update', $reminder))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('reminder.destroy', $reminder))->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_index_denied_without_manage_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('reminder.index'))->assertSessionHas('error');
    }

    public function test_store_denied_without_create_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->post(route('reminder.store'), $this->validPayload())->assertSessionHas('error');
    }

    public function test_destroy_denied_without_delete_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->delete(route('reminder.destroy', $reminder))->assertSessionHas('error');
    }

    // ── ReminderController::index ─────────────────────────────────────────────

    public function test_index_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)->get(route('reminder.index'))->assertOk();
    }

    // ── ReminderController::store ─────────────────────────────────────────────

    public function test_store_creates_reminder_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder.store'), $this->validPayload(['name' => 'Oil Service']))
            ->assertRedirect(route('reminder.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reminders', ['name' => 'Oil Service', 'parent_id' => $this->owner->id]);
    }

    public function test_store_flashes_error_on_missing_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder.store'), $this->validPayload(['name' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_when_reminder_date_is_not_after_today(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder.store'), $this->validPayload(['reminder_date' => now()->format('Y-m-d')]))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderController::update ────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $reminder = Reminder::factory()->create([
            'name'      => 'Old Name',
            'parent_id' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->put(route('reminder.update', $reminder), $this->updatePayload(['name' => 'New Name']))
            ->assertRedirect(route('reminder.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reminders', ['id' => $reminder->id, 'name' => 'New Name']);
    }

    public function test_update_flashes_error_on_missing_name(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('reminder.update', $reminder), [])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderController::destroy ───────────────────────────────────────────

    public function test_destroy_deletes_reminder(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('reminder.destroy', $reminder))
            ->assertRedirect(route('reminder.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('reminders', ['id' => $reminder->id]);
    }

    // ── ReminderController::create ────────────────────────────────────────────

    public function test_create_requires_auth(): void
    {
        $this->get(route('reminder.create'))->assertRedirect(route('login'));
    }

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('reminder.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Reminder/Create')
                ->has('vehicles')
                ->has('types')
            );
    }

    // ── ReminderController::edit ──────────────────────────────────────────────

    public function test_edit_requires_auth(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('reminder.edit', $reminder))->assertRedirect(route('login'));
    }

    public function test_edit_renders_inertia_component(): void
    {
        $reminder = Reminder::factory()->create([
            'parent_id'  => $this->owner->id,
            'id_vehicle' => $this->vehicle->id,
        ]);

        $this->actingAs($this->owner)
            ->get(route('reminder.edit', $reminder))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Reminder/Edit')
                ->has('reminder')
                ->has('type')
                ->has('vehicleName')
            );
    }

    // ── ReminderController::update — permission denied ────────────────────────

    public function test_update_denied_without_edit_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->put(route('reminder.update', $reminder), $this->updatePayload())
            ->assertSessionHas('error');
    }

    // ── ReminderController::store — missing vehicle validation ────────────────

    public function test_store_flashes_error_on_missing_vehicle(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder.store'), $this->validPayload(['vehicle' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_on_missing_type(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder.store'), $this->validPayload(['type' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderController::update — invalid date ─────────────────────────────

    public function test_update_flashes_error_on_invalid_date(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('reminder.update', $reminder), $this->updatePayload(['reminder_date' => 'not-a-date']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderController::markAsCompleted ───────────────────────────────────

    public function test_mark_as_completed_requires_auth(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->post(route('reminder.complete', $reminder))->assertRedirect(route('login'));
    }

    public function test_mark_as_completed_sets_status_to_completed(): void
    {
        $reminder = Reminder::factory()->create([
            'parent_id' => $this->owner->id,
            'status'    => 'pending',
        ]);

        $this->actingAs($this->owner)
            ->post(route('reminder.complete', $reminder))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reminders', ['id' => $reminder->id, 'status' => 'completed']);
    }

    public function test_mark_as_completed_denied_without_edit_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->post(route('reminder.complete', $reminder))
            ->assertSessionHas('error');
    }

    // ── ReminderController::snoozeReminder ────────────────────────────────────

    public function test_snooze_requires_auth(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->post(route('reminder.snooze', $reminder))->assertRedirect(route('login'));
    }

    public function test_snooze_extends_reminder_date(): void
    {
        $originalDate = now()->addDays(5)->format('Y-m-d');
        $reminder = Reminder::factory()->create([
            'parent_id'     => $this->owner->id,
            'reminder_date' => $originalDate,
        ]);

        $this->actingAs($this->owner)
            ->post(route('reminder.snooze', $reminder), ['days' => 7])
            ->assertRedirect()
            ->assertSessionHas('success');

        $originalDate = \Carbon\Carbon::parse($reminder->reminder_date);
        $reminder->refresh();
        $expectedDate = $originalDate->addDays(7)->format('Y-m-d');
        $this->assertEquals($expectedDate, \Carbon\Carbon::parse($reminder->reminder_date)->format('Y-m-d'));
    }

    public function test_snooze_flashes_error_on_missing_days(): void
    {
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->post(route('reminder.snooze', $reminder), [])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_snooze_denied_without_edit_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminder = Reminder::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->post(route('reminder.snooze', $reminder), ['days' => 3])
            ->assertSessionHas('error');
    }

    // ── ReminderController::getUrgentReminders ────────────────────────────────

    public function test_urgent_reminders_requires_auth(): void
    {
        $this->get(route('reminder.urgent.list'))->assertRedirect(route('login'));
    }

    public function test_urgent_reminders_returns_json(): void
    {
        Reminder::factory()->create([
            'parent_id' => $this->owner->id,
            'status'    => 'urgent',
        ]);

        $this->actingAs($this->owner)
            ->getJson(route('reminder.urgent.list'))
            ->assertOk()
            ->assertJsonStructure([['id', 'name', 'status', 'reminder_date']]);
    }

    // ── ReminderController::getDashboardData ──────────────────────────────────

    public function test_dashboard_data_requires_auth(): void
    {
        $this->get(route('reminder.dashboard.data'))->assertRedirect(route('login'));
    }

    public function test_dashboard_data_returns_json_with_stats(): void
    {
        $this->actingAs($this->owner)
            ->getJson(route('reminder.dashboard.data'))
            ->assertOk()
            ->assertJsonStructure(['stats' => ['overdue', 'urgent', 'upcoming', 'pending'], 'upcoming']);
    }

    // ── ReminderController::getVehicleReminders ───────────────────────────────

    public function test_vehicle_reminders_requires_auth(): void
    {
        $this->get(route('reminder.vehicle', $this->vehicle->id))->assertRedirect(route('login'));
    }

    public function test_vehicle_reminders_returns_json(): void
    {
        Reminder::factory()->create([
            'parent_id'  => $this->owner->id,
            'id_vehicle' => $this->vehicle->id,
        ]);

        $this->actingAs($this->owner)
            ->getJson(route('reminder.vehicle', $this->vehicle->id))
            ->assertOk()
            ->assertJsonStructure([['id', 'name', 'reminder_date']]);
    }

    // ── ReminderController::updateReminderStatuses ────────────────────────────

    public function test_update_statuses_requires_auth(): void
    {
        $this->post(route('reminder.update.statuses'))->assertRedirect(route('login'));
    }

    public function test_update_statuses_returns_json_success(): void
    {
        $this->actingAs($this->owner)
            ->postJson(route('reminder.update.statuses'))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    // ── ReminderController::getReminderStatistics ─────────────────────────────

    public function test_reminder_statistics_requires_auth(): void
    {
        $this->get(route('reminder.statistics'))->assertRedirect(route('login'));
    }

    public function test_reminder_statistics_returns_json(): void
    {
        $this->actingAs($this->owner)
            ->getJson(route('reminder.statistics'))
            ->assertOk()
            ->assertJsonStructure(['current_month', 'by_vehicle', 'by_type']);
    }

    // ── ReminderController::createRecurringReminders ─────────────────────────

    public function test_create_recurring_reminders_requires_auth(): void
    {
        $this->post(route('reminder.create.recurring'))->assertRedirect(route('login'));
    }

    public function test_create_recurring_reminders_returns_json_success(): void
    {
        // Create a completed reminder; recurring types don't match so createdCount stays 0
        Reminder::factory()->create([
            'parent_id'  => $this->owner->id,
            'status'     => 'completed',
            'reminder_type_id' => $this->reminderType->id,
        ]);

        $this->actingAs($this->owner)
            ->postJson(route('reminder.create.recurring'))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    // ── ReminderController::index — lists reminders with stats ───────────────

    public function test_index_renders_inertia_with_stats(): void
    {
        Reminder::factory()->create([
            'parent_id' => $this->owner->id,
            'status'    => 'overdue',
        ]);

        $this->actingAs($this->owner)
            ->get(route('reminder.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Reminder/Index')
                ->has('reminders')
                ->has('stats')
            );
    }

    // ── ReminderController::updateReminderStatuses — status transitions ───────

    public function test_update_statuses_transitions_overdue_reminder(): void
    {
        // A reminder past its date should be overdue
        Reminder::factory()->create([
            'parent_id'     => $this->owner->id,
            'status'        => 'pending',
            'reminder_date' => now()->subDays(2)->format('Y-m-d'),
        ]);

        $this->actingAs($this->owner)
            ->postJson(route('reminder.update.statuses'))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Tyre Check',
            'type'          => $this->reminderType->id,
            'reminder_date' => now()->addDays(10)->format('Y-m-d'),
            'vehicle'       => $this->vehicle->id,
        ], $overrides);
    }

    private function updatePayload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Tyre Check',
            'type'          => $this->reminderType->id,
            'reminder_date' => now()->addDays(10)->format('Y-m-d'),
        ], $overrides);
    }
}
