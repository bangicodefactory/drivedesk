<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        $perms = ['manage driver', 'create driver', 'edit driver', 'delete driver'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        Role::create(['name' => 'driver', 'guard_name' => 'web', 'parent_id' => $this->owner->id]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('driver.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('driver.store'))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->put(route('driver.update', $driverUser->id))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('driver.destroy', $driverUser->id))->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_index_denied_without_manage_driver(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('driver.index'))->assertSessionHas('error');
    }

    public function test_store_denied_without_create_driver(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->post(route('driver.store'), $this->validPayload())->assertSessionHas('error');
    }

    public function test_destroy_denied_without_delete_driver(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->delete(route('driver.destroy', $driverUser->id))->assertSessionHas('error');
    }

    // ── DriverController::index ───────────────────────────────────────────────

    public function test_index_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)->get(route('driver.index'))->assertOk();
    }

    // ── DriverController::store ───────────────────────────────────────────────

    public function test_store_creates_driver_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.store'), $this->validPayload(['first_name' => 'Jane', 'last_name' => 'Doe']))
            ->assertRedirect(route('driver.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['name' => 'Jane Doe', 'type' => 'driver', 'parent_id' => $this->owner->id]);
    }

    public function test_store_flashes_error_on_underage_driver(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.store'), $this->validPayload([
                'birth_date' => Carbon::now()->subYears(17)->format('Y-m-d'),
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_on_missing_first_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.store'), $this->validPayload(['first_name' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── DriverController::update ──────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $driverUser = User::factory()->driver()->create([
            'name'      => 'Old Name',
            'email'     => 'old@example.com',
            'parent_id' => $this->owner->id,
        ]);
        // DriverFactory uses string driver_id ('DR-####') which violates the integer column constraint.
        Driver::create(['driver_id' => $driverUser->id, 'user_id' => $driverUser->id, 'gender' => 'Male', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('driver.update', $driverUser->id), [
                'first_name' => 'New',
                'last_name'  => 'Name',
                'email'      => 'new@example.com',
            ])
            ->assertRedirect(route('driver.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $driverUser->id, 'name' => 'New Name']);
    }

    public function test_update_flashes_error_on_missing_first_name(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('driver.update', $driverUser->id), [])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── DriverController::destroy ─────────────────────────────────────────────

    public function test_destroy_deletes_driver(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        Driver::create(['driver_id' => $driverUser->id, 'user_id' => $driverUser->id, 'gender' => 'Male', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('driver.destroy', $driverUser->id))
            ->assertRedirect(route('driver.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $driverUser->id]);
    }

    // ── DriverController::show ────────────────────────────────────────────────

    public function test_show_requires_auth(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->get(route('driver.show', $driverUser->id))->assertRedirect(route('login'));
    }

    public function test_show_renders_inertia_component(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        Driver::create(['driver_id' => $driverUser->id, 'user_id' => $driverUser->id, 'gender' => 'Male', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('driver.show', $driverUser->id))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Driver/Show')
                ->has('driver')
                ->has('user')
            );
    }

    // ── DriverController::edit ────────────────────────────────────────────────

    public function test_edit_requires_auth(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->get(route('driver.edit', $driverUser->id))->assertRedirect(route('login'));
    }

    public function test_edit_renders_inertia_component(): void
    {
        $driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        Driver::create(['driver_id' => $driverUser->id, 'user_id' => $driverUser->id, 'gender' => 'Male', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('driver.edit', $driverUser->id))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Driver/Edit')
                ->has('gender')
                ->has('user')
            );
    }

    // ── DriverController::create ──────────────────────────────────────────────

    public function test_create_requires_auth(): void
    {
        $this->get(route('driver.create'))->assertRedirect(route('login'));
    }

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('driver.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Driver/Create')
                ->has('gender')
            );
    }

    // ── DriverController::store — with explicit email ─────────────────────────

    public function test_store_creates_driver_with_explicit_email(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.store'), $this->validPayload([
                'first_name' => 'Maria',
                'last_name'  => 'Silva',
                'email'      => 'maria.silva@test.com',
            ]))
            ->assertRedirect(route('driver.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'maria.silva@test.com', 'type' => 'driver']);
    }

    // ── DriverController::update — with document upload ───────────────────────

    public function test_update_with_document_upload_saves_successfully(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $driverUser = User::factory()->driver()->create([
            'name'      => 'Old Name',
            'email'     => 'old@example.com',
            'parent_id' => $this->owner->id,
        ]);
        Driver::create(['driver_id' => $driverUser->id, 'user_id' => $driverUser->id, 'gender' => 'Male', 'parent_id' => $this->owner->id]);

        $document = \Illuminate\Http\UploadedFile::fake()->create('id_card.pdf', 100, 'application/pdf');

        $this->actingAs($this->owner)
            ->put(route('driver.update', $driverUser->id), [
                'first_name' => 'New',
                'last_name'  => 'Name',
                'email'      => 'new@example.com',
                'document'   => $document,
            ])
            ->assertRedirect(route('driver.index'))
            ->assertSessionHas('success');
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name'      => 'John',
            'last_name'       => 'Driver',
            'phone_number'    => '0612345678',
            'gender'          => 'Male',
            'birth_date'      => Carbon::now()->subYears(25)->format('Y-m-d'),
            'address'         => '123 Main St',
            'license_number'  => 'LIC-123456',
            'issue_date'      => now()->subYears(2)->format('Y-m-d'),
            'expiration_date' => now()->addYears(3)->format('Y-m-d'),
        ], $overrides);
    }
}
