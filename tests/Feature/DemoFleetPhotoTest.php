<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DevDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * DevDataSeeder::ensureFleetPhoto() (BAN-331).
 *
 * The demo fleet ships with stock photographs, written onto the public disk so
 * they behave like uploads. Three behaviours make that safe, and none of them
 * was covered when the feature first landed:
 *
 *  - an admin's own upload survives the nightly demo:seed;
 *  - a corrected fixture actually reaches an environment that already has the
 *    old one; and
 *  - a failed write does not leave the row pointing at a file that is not there.
 *
 * Storage::fake keeps the suite off the real public disk. Before this, running
 * the seeder in a test wrote a megabyte of JPEGs into the developer's own
 * storage/ and left them there, which is what let a stale copy survive.
 */
class DemoFleetPhotoTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    /** One fixture that really ships, so the assertions are about real bytes. */
    private const PHOTO = 'renault-clio.jpg';
    private const PATH  = 'upload/picture/' . self::PHOTO;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');
        Storage::fake('public');

        // DevDataSeeder resolves the owner with firstOrFail().
        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
    }

    private function fixtureBytes(): string
    {
        return file_get_contents(database_path('seeders/fixtures/fleet/' . self::PHOTO));
    }

    private function clio(): Vehicle
    {
        return Vehicle::where('name', 'Renault Clio')->firstOrFail();
    }

    public function test_it_writes_the_fixture_and_points_the_vehicle_at_it(): void
    {
        $this->seed(DevDataSeeder::class);

        Storage::disk('public')->assertExists(self::PATH);
        $this->assertSame($this->fixtureBytes(), Storage::disk('public')->get(self::PATH));
        $this->assertSame(self::PHOTO, $this->clio()->picture);
    }

    /**
     * The promise that matters to a real customer: an admin photographs their
     * actual car, uploads it, and the 03:30 demo refresh leaves it alone.
     */
    public function test_it_never_replaces_a_picture_the_admin_uploaded(): void
    {
        $this->seed(DevDataSeeder::class);

        $clio = $this->clio();
        $clio->picture = 'the-real-car.jpg';
        $clio->save();

        $this->seed(DevDataSeeder::class);

        $this->assertSame('the-real-car.jpg', $this->clio()->fresh()->picture);
    }

    /**
     * A fixture corrected in a later release has to reach a deployment that
     * already copied the wrong one. Copying by filename alone did not: the
     * first, wrong version of this fleet — a police-spec Ford Explorer filed as
     * a RAV4 — was frozen onto every environment that had run the seeder once.
     */
    public function test_it_rewrites_a_fixture_that_has_diverged_from_the_repo(): void
    {
        $this->seed(DevDataSeeder::class);

        Storage::disk('public')->put(self::PATH, 'a stale image from an earlier release');

        $this->seed(DevDataSeeder::class);

        $this->assertSame($this->fixtureBytes(), Storage::disk('public')->get(self::PATH));
    }

    /**
     * Not every seeded vehicle has a fixture — three ship without one on
     * purpose. Those keep a null picture and the storefront's own placeholder,
     * and the run does not fall over.
     */
    public function test_a_vehicle_without_a_fixture_keeps_the_default_placeholder(): void
    {
        $this->seed(DevDataSeeder::class);

        $this->assertNull(Vehicle::where('name', 'Dacia Duster')->firstOrFail()->picture);
        $this->assertNull(Vehicle::where('name', 'Mercedes GLE')->firstOrFail()->picture);
        $this->assertNull(Vehicle::where('name', 'Volkswagen T-Roc')->firstOrFail()->picture);
    }

    /** And the ones that do have a fixture all actually land. */
    public function test_every_declared_fixture_reaches_the_disk(): void
    {
        $this->seed(DevDataSeeder::class);

        foreach (['toyota-rav4.jpg', 'renault-clio.jpg', 'peugeot-208.jpg', 'ford-transit.jpg'] as $photo) {
            Storage::disk('public')->assertExists('upload/picture/' . $photo);
            $this->assertDatabaseHas('vehicles', ['picture' => $photo]);
        }
    }
}
