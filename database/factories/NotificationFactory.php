<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'module'        => $this->faker->randomElement(['new_booking', 'new_driver', 'user_create', 'new_agreement']),
            'name'          => $this->faker->words(2, true),
            'subject'       => $this->faker->sentence(),
            'message'       => $this->faker->paragraph(),
            'short_code'    => '{company_name}',
            'enabled_email' => 1,
            // BAN-297: no enabled_sms. It survives in Notification::$fillable but
            // no migration ever created the column, and factories build instances
            // inside Model::unguarded() so $fillable does not filter it out --
            // every create() died with "Unknown column 'enabled_sms'". That is
            // why NotificationControllerTest and UserControllerTest build their
            // rows with Notification::create() instead of calling the factory.
            'parent_id'     => 0,
        ];
    }
}
