<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrafficViolation>
 */
class TrafficViolationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id'        => 0,
            'reference'        => $this->faker->unique()->bothify('PV-######'),
            'authority'        => 'Police',
            'license_plate'    => strtoupper($this->faker->bothify('##### ? ##')),
            'occurred_at'      => now()->subDays(3)->setTime(14, 32),
            'notice_date'      => now()->format('Y-m-d'),
            'location'         => $this->faker->streetName(),
            'description'      => 'Excès de vitesse',
            'amount'           => $this->faker->randomFloat(2, 150, 900),
            'vehicle_id'       => null,
            'booking_id'       => null,
            'driver_user_id'   => null,
            'match_confidence' => 'none',
            'match_source'     => null,
            'matched_at'       => null,
            'confirmed_by'     => null,
            'confirmed_at'     => null,
            'status'           => 'new',
            'liable_party'     => 'unknown',
            'amount_recovered' => 0,
            'document'         => null,
            'notes'            => null,
            'created_by'       => null,
        ];
    }

    /** A violation the owner has already pinned to a rental by hand. */
    public function manuallyMatched(int $bookingId, int $driverUserId): static
    {
        return $this->state(fn () => [
            'booking_id'       => $bookingId,
            'driver_user_id'   => $driverUserId,
            'match_confidence' => 'exact',
            'match_source'     => 'manual',
            'matched_at'       => now(),
            'confirmed_at'     => now(),
        ]);
    }
}
