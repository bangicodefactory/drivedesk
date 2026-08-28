<?php

namespace Tests\Feature;

use App\Models\ReminderType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class ReminderTypeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $perms = ['manage reminder', 'create reminder', 'edit reminder', 'delete reminder'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('reminder-type.index'))->assertRedirect(route('login'));
    }

    public function test_create_requires_auth(): void
    {
        $this->get(route('reminder-type.create'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('reminder-type.store'))->assertRedirect(route('login'));
    }

    public function test_edit_requires_auth(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('reminder-type.edit', $reminderType))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
        $this->put(route('reminder-type.update', $reminderType))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('reminder-type.destroy', $reminderType))->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_index_denied_without_manage_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('reminder-type.index'))->assertSessionHas('error');
    }

    public function test_store_denied_without_create_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->post(route('reminder-type.store'), ['type' => 'Oil Change'])
            ->assertSessionHas('error');
    }

    public function test_update_denied_without_edit_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->put(route('reminder-type.update', $reminderType), ['type' => 'New Type'])
            ->assertSessionHas('error');
    }

    public function test_destroy_denied_without_delete_reminder(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->delete(route('reminder-type.destroy', $reminderType))
            ->assertSessionHas('error');
    }

    // ── ReminderTypeController::index ─────────────────────────────────────────

    public function test_index_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)
            ->get(route('reminder-type.index'))
            ->assertOk();
    }

    public function test_index_renders_inertia_component(): void
    {
        ReminderType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('reminder-type.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ReminderType/Index')
                ->has('types')
            );
    }

    // ── ReminderTypeController::create ────────────────────────────────────────

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('reminder-type.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ReminderType/Create'));
    }

    // ── ReminderTypeController::store ─────────────────────────────────────────

    public function test_store_creates_reminder_type_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder-type.store'), ['type' => 'Oil Change'])
            ->assertRedirect(route('reminder-type.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reminder_types', ['type' => 'Oil Change', 'parent_id' => $this->owner->id]);
    }

    public function test_store_flashes_error_on_missing_type(): void
    {
        $this->actingAs($this->owner)
            ->post(route('reminder-type.store'), ['type' => ''])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderTypeController::edit ──────────────────────────────────────────

    public function test_edit_renders_inertia_component(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('reminder-type.edit', $reminderType))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ReminderType/Edit')
                ->has('reminderType')
            );
    }

    // ── ReminderTypeController::update ────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $reminderType = ReminderType::factory()->create([
            'type'      => 'Old Type',
            'parent_id' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->put(route('reminder-type.update', $reminderType), ['type' => 'New Type'])
            ->assertRedirect(route('reminder-type.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reminder_types', ['id' => $reminderType->id, 'type' => 'New Type']);
    }

    public function test_update_flashes_error_on_missing_type(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('reminder-type.update', $reminderType), ['type' => ''])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── ReminderTypeController::destroy ───────────────────────────────────────

    public function test_destroy_deletes_reminder_type(): void
    {
        $reminderType = ReminderType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('reminder-type.destroy', $reminderType))
            ->assertRedirect(route('reminder-type.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('reminder_types', ['id' => $reminderType->id]);
    }
}
