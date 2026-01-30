<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample driver users and their driver records.
     *
     * @return void
     */
    public function run()
    {
        $owner = User::where('type', 'owner')->first();
        if (!$owner) {
            $this->command->warn('No owner user found. Skipping driver seed. Run DefaultDataUsersTableSeeder first.');

            return;
        }

        $driverRole = Role::where('name', 'driver')->where('parent_id', $owner->id)->first();
        if (!$driverRole) {
            $this->command->warn('Driver role not found for owner. Skipping driver seed.');

            return;
        }

        $parentId = $owner->id;
        $driversData = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.driver@example.com',
                'phone_number' => '0612345678',
                'gender' => 'Male',
                'birth_date' => '1990-05-15',
                'address' => '123 Rue Example, Casablanca',
                'license_number' => 'DL-001-2020',
                'issue_date' => '2020-01-10',
                'expiration_date' => '2026-01-10',
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.driver@example.com',
                'phone_number' => '0623456789',
                'gender' => 'Female',
                'birth_date' => '1988-08-22',
                'address' => '45 Avenue Test, Rabat',
                'license_number' => 'DL-002-2019',
                'issue_date' => '2019-06-01',
                'expiration_date' => '2025-06-01',
            ],
            [
                'name' => 'Youssef El Amrani',
                'email' => 'youssef.driver@example.com',
                'phone_number' => '0634567890',
                'gender' => 'Male',
                'birth_date' => '1992-03-08',
                'address' => '78 Boulevard Demo, Marrakech',
                'license_number' => 'DL-003-2021',
                'issue_date' => '2021-02-15',
                'expiration_date' => '2027-02-15',
            ],
        ];

        foreach ($driversData as $data) {
            if (User::where('email', $data['email'])->exists()) {
                continue;
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('123456'),
                'type' => 'driver',
                'phone_number' => $data['phone_number'],
                'profile' => 'avatar.png',
                'lang' => 'english',
                'parent_id' => $parentId,
                'is_active' => 1,
            ]);
            $user->assignRole($driverRole);

            $driverId = $this->nextDriverId($parentId);

            Driver::create([
                'driver_id' => $driverId,
                'user_id' => $user->id,
                'gender' => $data['gender'],
                'age' => (int) Carbon::parse($data['birth_date'])->diffInYears(Carbon::now()),
                'address' => $data['address'],
                'birth_date' => $data['birth_date'],
                'license_number' => $data['license_number'],
                'issue_date' => $data['issue_date'],
                'expiration_date' => $data['expiration_date'],
                'parent_id' => $parentId,
            ]);
        }

        $this->command->info('Driver seeding completed.');
    }

    /**
     * Get next driver_id for the given parent.
     */
    private function nextDriverId(int $parentId): int
    {
        $latest = Driver::where('parent_id', $parentId)->orderByDesc('driver_id')->first();

        return $latest ? $latest->driver_id + 1 : 1;
    }
}
