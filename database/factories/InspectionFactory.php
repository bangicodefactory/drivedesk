<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inspection>
 */
class InspectionFactory extends Factory
{
    /**
     * Only columns that exist on `inspections`.
     *
     * BAN-297: this definition used to set meter_reading_outgoing, outgoing_date,
     * outgoing_time and incoming_time. None of them are columns — they survive in
     * Inspection::$fillable but no migration ever created them. Factories build
     * instances inside Model::unguarded(), so $fillable does not filter them out
     * and every create() died with "Unknown column 'meter_reading_outgoing'".
     * That is why InspectionControllerTest and InertiaInspectionTest hand-roll
     * their rows with Inspection::create() instead of calling the factory.
     *
     * meter_reading_incoming and amount are NOT NULL with a default, so they take
     * a value rather than null; parent_id is NOT NULL with no default at all.
     */
    public function definition(): array
    {
        return [
            'vehicle'                => Vehicle::factory(),
            'inspector'              => User::factory(),
            'inspection_date'        => now()->format('Y-m-d'),
            'meter_reading_incoming' => $this->faker->numberBetween(0, 100000),
            'incoming_date'          => now()->format('Y-m-d'),
            'details'                => null,
            'notes'                  => null,
            'status'                 => 'pending',
            'repair_status'          => 'pending',
            'amount'                 => 0,
            'receipt'                => null,
            'parent_id'              => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
