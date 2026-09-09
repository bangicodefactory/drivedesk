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
     * Every seeded vehicle gets a photo, and every declared fixture lands.
     *
     * A vehicle may still legitimately have no `photo` key — the seeder treats
     * it as optional and the storefront falls back to its own placeholder — but
     * none does today, so this asserts the whole fleet rather than a subset.
     * A fixture that stops shipping shows up here as a missing file rather than
     * as a quietly blank card.
     */
    public function test_every_seeded_vehicle_gets_its_fixture(): void
    {
        $this->seed(DevDataSeeder::class);

        $expected = [
            'Toyota RAV4'      => 'toyota-rav4.jpg',
            'Dacia Duster'     => 'dacia-duster.jpg',
            'Renault Clio'     => 'renault-clio.jpg',
            'Mercedes GLE'     => 'mercedes-gle.jpg',
            'Peugeot 208'      => 'peugeot-208.jpg',
            'Volkswagen T-Roc' => 'volkswagen-t-roc.jpg',
            'Ford Transit'     => 'ford-transit.jpg',
        ];

        foreach ($expected as $name => $photo) {
            $this->assertSame(
                $photo,
                Vehicle::where('name', $name)->firstOrFail()->picture,
                "{$name} did not get its fixture"
            );
            Storage::disk('public')->assertExists('upload/picture/' . $photo);
        }

        $this->assertSame(0, Vehicle::whereNull('picture')->count(), 'a seeded vehicle was left without a picture');
    }
}
