<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Bulk import of traffic violation notices (BAN-260): every row auto-matched,
 * bad rows reported rather than aborting the batch, and a re-imported file
 * producing no duplicates.
 */
class TrafficViolationImportTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private const PERMISSIONS = [
        'manage traffic violation',
        'create traffic violation',
        'edit traffic violation',
        'delete traffic violation',
    ];

    private const HEADER = ['REFERENCE', 'IMMATRICULATION', 'DATE', 'HEURE', 'LIEU', 'INFRACTION', 'MONTANT'];

    protected User $owner;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(self::PERMISSIONS);

        $this->vehicle = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
        ]);
    }

    /** @param array<int,array<int,mixed>> $rows */
    private function upload(array $rows, string $name = 'violations.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray(array_merge([self::HEADER], $rows), null, 'A1');

        $path = storage_path('app/'.uniqid('test_import_').'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function bookingCovering(): Booking
    {
        return Booking::factory()->create([
            'parent_id'  => $this->owner->id,
            'vehicle'    => $this->vehicle->id,
            'driver'     => User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver'])->id,
            'start_date' => '2026-06-01',
            'start_time' => '09:00:00',
            'end_date'   => '2026-06-05',
            'end_time'   => '18:00:00',
            'status'     => 'completed',
        ]);
    }

    private function validRow(array $overrides = []): array
    {
        return array_replace(
            ['PV-001', '12345 A 6', '03/06/2026', '14:32', 'Avenue Hassan II', 'Excès de vitesse', '400'],
            $overrides
        );
    }

    // ── Access ───────────────────────────────────────────────────────────────

    public function test_import_requires_auth(): void
    {
        $this->post(route('traffic-violation.import'))->assertRedirect(route('login'));
    }

    public function test_import_denied_without_create_permission(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->post(route('traffic-violation.import'), ['file' => $this->upload([$this->validRow()])])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_template_downloads(): void
    {
        $this->actingAs($this->owner)
            ->get(route('traffic-violation.template'))
            ->assertOk()
            ->assertDownload('traffic_violations_template.xlsx');
    }

    public function test_template_requires_auth(): void
    {
        $this->get(route('traffic-violation.template'))->assertRedirect(route('login'));
    }

    // ── Happy path ───────────────────────────────────────────────────────────

    public function test_import_creates_violations_and_auto_matches_them(): void
    {
        $booking = $this->bookingCovering();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.import'), ['file' => $this->upload([$this->validRow()])])
            ->assertRedirect(route('traffic-violation.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('traffic_violations', [
            'reference'        => 'PV-001',
            'parent_id'        => $this->owner->id,
            'license_plate'    => '12345 A 6',
            'vehicle_id'       => $this->vehicle->id,
            'booking_id'       => $booking->id,
            'driver_user_id'   => $booking->getAttributes()['driver'],
            'match_confidence' => 'exact',
            'match_source'     => 'auto',
        ]);
    }

    public function test_import_reads_dates_day_first(): void
    {
        // 03/06/2026 is 3 June, never 6 March (IST-231).
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.import'), ['file' => $this->upload([$this->validRow()])]);

        $this->assertSame(
            '2026-06-03 14:32:00',
            TrafficViolation::first()->occurred_at->format('Y-m-d H:i:s')
        );
    }

    public function test_import_records_no_match_when_nothing_covers_the_instant(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.import'), ['file' => $this->upload([$this->validRow()])]);

        $this->assertDatabaseHas('traffic_violations', [
            'reference'        => 'PV-001',
            'booking_id'       => null,
            'match_confidence' => 'none',
        ]);
    }

    public function test_import_handles_several_rows(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([
                $this->validRow(),
                $this->validRow([0 => 'PV-002', 3 => '09:15']),
                $this->validRow([0 => 'PV-003', 2 => '04/06/2026']),
            ]),
        ]);

        $this->assertSame(3, TrafficViolation::count());
    }

    // ── Bad rows ─────────────────────────────────────────────────────────────

    public function test_import_skips_an_unreadable_date_but_keeps_the_good_rows(): void
    {
        $response = $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([
                $this->validRow(),
                $this->validRow([0 => 'PV-BAD', 2 => 'not-a-date']),
            ]),
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(1, TrafficViolation::count());

        $skipped = session('import_skipped');
        $this->assertIsArray($skipped);
        $this->assertCount(1, $skipped);
        $this->assertStringContainsString('3', $skipped[0]); // spreadsheet line number
    }

    public function test_import_skips_a_row_with_no_plate(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow([1 => ''])]),
        ]);

        $this->assertSame(0, TrafficViolation::count());
        $this->assertCount(1, session('import_skipped'));
    }

    public function test_import_skips_an_unreadable_time(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow([3 => 'lunchtime'])]),
        ]);

        $this->assertSame(0, TrafficViolation::count());
        $this->assertCount(1, session('import_skipped'));
    }

    public function test_import_ignores_blank_filler_rows(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([
                $this->validRow(),
                ['', '', '', '', '', '', ''],
            ]),
        ]);

        $this->assertSame(1, TrafficViolation::count());
        $this->assertNull(session('import_skipped'));
    }

    // ── Idempotence ──────────────────────────────────────────────────────────

    public function test_reimporting_the_same_file_creates_no_duplicates(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow()]),
        ]);
        $this->assertSame(1, TrafficViolation::count());

        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow()]),
        ]);

        $this->assertSame(1, TrafficViolation::count());
        $this->assertCount(1, session('import_skipped'));
    }

    public function test_a_duplicate_reference_within_one_file_is_reported_once(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow(), $this->validRow()]),
        ]);

        $this->assertSame(1, TrafficViolation::count());
        $this->assertCount(1, session('import_skipped'));
    }

    public function test_rows_without_a_reference_are_all_imported(): void
    {
        // A blank reference must not collide on the unique index.
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([
                $this->validRow([0 => '']),
                $this->validRow([0 => '', 3 => '09:15']),
            ]),
        ]);

        $this->assertSame(2, TrafficViolation::whereNull('reference')->count());
    }

    public function test_another_tenants_reference_does_not_block_the_import(): void
    {
        TrafficViolation::factory()->create(['parent_id' => 9999, 'reference' => 'PV-001']);

        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow()]),
        ]);

        $this->assertSame(1, TrafficViolation::where('parent_id', $this->owner->id)->count());
    }

    // ── File handling ────────────────────────────────────────────────────────

    public function test_import_rejects_a_file_with_only_a_header(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.import'), ['file' => $this->upload([])])
            ->assertSessionHas('error');
    }

    public function test_import_requires_a_file(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.import'), [])
            ->assertSessionHasErrors('file');
    }

    public function test_skipped_rows_reach_inertia_as_a_flash_prop(): void
    {
        $this->actingAs($this->owner)->post(route('traffic-violation.import'), [
            'file' => $this->upload([$this->validRow([2 => 'nope'])]),
        ]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index'))
            ->assertInertia(fn (Assert $page) => $page->has('flash.import_skipped', 1));
    }
}
