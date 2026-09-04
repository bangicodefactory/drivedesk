<?php

namespace Tests\Feature;

use App\Models\Tva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class TvaRenumberControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $outsider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        // BAN-304: this block used to read "TvaRenumberController has no can()
        // permission check — any authenticated user may call all three routes.
        // Tests below document that behavior." It was an accurate description of
        // a hole, written as though it were the specification. apply() rewrites
        // facture numbers on legally numbered documents; previewJson() returns
        // every number and date for a year. Both are now gated on `manage tva`,
        // and the denial cases are covered below.
        Permission::firstOrCreate(['name' => 'manage tva', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // The flag is forced rather than inherited from the acme fixture:
        // asClient() picks a realistic tenant and must not supply the flag under
        // test (CLAUDE.md 10.2.6).
        config(['client.features.tva_renumber' => true]);

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo('manage tva');

        // Authenticated, same tenant, no TVA permission — the case the old
        // comment declared allowed.
        $this->outsider = User::factory()->create(['type' => 'user', 'parent_id' => $this->owner->id]);
    }

    // ── authorization (BAN-304) ───────────────────────────────────────────────

    public function test_index_is_denied_without_manage_tva(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('tva.renumber.index'))
            ->assertRedirect();
    }

    public function test_preview_is_denied_without_manage_tva(): void
    {
        $this->actingAs($this->outsider)
            ->getJson(route('tva.renumber.preview', ['year' => 2025]))
            ->assertForbidden();
    }

    public function test_apply_is_denied_without_manage_tva_and_changes_nothing(): void
    {
        $tva = Tva::factory()->withInvoice()->create([
            'parent_id'      => $this->owner->id,
            'facture_date'   => '2025-04-01',
            'facture_number' => 'ORIGINAL-9',
        ]);

        $this->actingAs($this->outsider)
            ->post(route('tva.renumber.apply'), ['year' => 2025])
            ->assertRedirect();

        $this->assertSame('ORIGINAL-9', $tva->fresh()->facture_number);
    }

    // ── feature flag (BAN-304) ────────────────────────────────────────────────

    public function test_the_routes_404_when_tva_renumber_is_off(): void
    {
        config(['client.features.tva_renumber' => false]);

        $this->actingAs($this->owner)->get(route('tva.renumber.index'))->assertNotFound();
        $this->actingAs($this->owner)->getJson(route('tva.renumber.preview', ['year' => 2025]))->assertNotFound();
        $this->actingAs($this->owner)->post(route('tva.renumber.apply'), ['year' => 2025])->assertNotFound();
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('tva.renumber.index'))->assertRedirect(route('login'));
    }

    public function test_apply_requires_auth(): void
    {
        $this->post(route('tva.renumber.apply'), ['year' => 2025])->assertRedirect(route('login'));
    }

    public function test_preview_requires_auth(): void
    {
        $this->get(route('tva.renumber.preview', ['year' => 2025]))->assertRedirect(route('login'));
    }

    // ── TvaRenumberController::index ──────────────────────────────────────────

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $this->actingAs($this->owner)
            ->get(route('tva.renumber.index'))
            ->assertOk();
    }

    public function test_index_accepts_year_query_param(): void
    {
        Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2024-06-01', 'facture_number' => '1']);

        $this->actingAs($this->owner)
            ->get(route('tva.renumber.index', ['year' => 2024]))
            ->assertOk();
    }

    // ── TvaRenumberController::previewJson ────────────────────────────────────

    public function test_preview_returns_json_with_correct_structure(): void
    {
        Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-03-01', 'facture_number' => '5']);

        $this->actingAs($this->owner)
            ->getJson(route('tva.renumber.preview', ['year' => 2025]))
            ->assertOk()
            ->assertJsonStructure(['year', 'count', 'records'])
            ->assertJsonFragment(['year' => 2025, 'count' => 1]);
    }

    public function test_preview_returns_empty_records_for_year_with_no_invoices(): void
    {
        $this->actingAs($this->owner)
            ->getJson(route('tva.renumber.preview', ['year' => 2020]))
            ->assertOk()
            ->assertJson(['year' => 2020, 'count' => 0, 'records' => []]);
    }

    public function test_preview_rejects_year_below_2020(): void
    {
        $this->actingAs($this->owner)
            ->getJson(route('tva.renumber.preview', ['year' => 2019]))
            ->assertUnprocessable();
    }

    public function test_preview_rejects_year_above_current_plus_one(): void
    {
        $tooFar = now()->year + 2;

        $this->actingAs($this->owner)
            ->getJson(route('tva.renumber.preview', ['year' => $tooFar]))
            ->assertUnprocessable();
    }

    // ── TvaRenumberController::apply ──────────────────────────────────────────

    public function test_apply_renumbers_invoices_and_redirects_with_success(): void
    {
        Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-01-15', 'facture_number' => '10']);
        Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-07-20', 'facture_number' => '50']);

        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2025])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_apply_persists_sequential_numbers(): void
    {
        $t1 = Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-02-01', 'facture_number' => '100']);
        $t2 = Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-09-01', 'facture_number' => '200']);

        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2025]);

        $this->assertDatabaseHas('tvas', ['id' => $t1->id, 'facture_number' => '1']);
        $this->assertDatabaseHas('tvas', ['id' => $t2->id, 'facture_number' => '2']);
    }

    public function test_apply_is_idempotent(): void
    {
        $t1 = Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-04-01', 'facture_number' => 'X']);

        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2025]);

        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2025])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tvas', ['id' => $t1->id, 'facture_number' => '1']);
    }

    public function test_apply_does_not_touch_other_years(): void
    {
        $other = Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 
            'facture_date'   => '2024-12-31',
            'facture_number' => 'UNTOUCHED',
        ]);
        Tva::factory()->withInvoice()->create(['parent_id' => $this->owner->id, 'facture_date' => '2025-01-01', 'facture_number' => 'X']);

        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2025]);

        $this->assertDatabaseHas('tvas', ['id' => $other->id, 'facture_number' => 'UNTOUCHED']);
    }

    public function test_apply_rejects_missing_year(): void
    {
        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['year']);
    }

    public function test_apply_rejects_year_below_2020(): void
    {
        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => 2019])
            ->assertRedirect()
            ->assertSessionHasErrors(['year']);
    }

    public function test_apply_rejects_year_above_current_plus_one(): void
    {
        $this->actingAs($this->owner)
            ->post(route('tva.renumber.apply'), ['year' => now()->year + 2])
            ->assertRedirect()
            ->assertSessionHasErrors(['year']);
    }
}
