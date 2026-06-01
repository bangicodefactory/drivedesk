<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Coupon;
use App\Models\Credit;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Inspection;
use App\Models\InspectionType;
use App\Models\Option;
use App\Models\Place;
use App\Models\Reminder;
use App\Models\ReminderType;
use App\Models\RentalAgreement;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds realistic test data for every business table, scoped to the owner.
 * Safe to re-run: guards with exists() checks on unique columns.
 *
 * Usage:
 *   php artisan db:seed --class=DevDataSeeder
 */
class DevDataSeeder extends Seeder
{
    private int $ownerId;

    public function run(): void
    {
        $owner = User::where('type', 'owner')->firstOrFail();
        $this->ownerId = $owner->id;

        $this->command->info("Seeding dev data for owner #{$this->ownerId} ({$owner->email})");

        // Clean up any stale test data seeded with parent_id=1
        $this->cleanStaleData();

        $vehicleTypeIds = $this->seedVehicleTypes();
        $placeIds       = $this->seedPlaces();
        $vehicleIds     = $this->seedVehicles($vehicleTypeIds);
        $driverUserIds  = $this->resolveDriverUserIds();
        $expenseTypeIds = $this->seedExpenseTypes();
        $inspTypeIds    = $this->seedInspectionTypes();
        $remTypeIds     = $this->seedReminderTypes();
        $addonIds       = $this->seedAddons();
                          $this->seedOptions();
        $bookingIds     = $this->seedBookings($vehicleIds, $driverUserIds, $placeIds, $addonIds);
                          $this->seedBookingPayments($bookingIds);
                          $this->seedExpenses($vehicleIds, $expenseTypeIds);
                          $this->seedInspections($vehicleIds, $inspTypeIds);
                          $this->seedReminders($vehicleIds, $remTypeIds);
                          $this->seedRentalAgreements($vehicleIds, $driverUserIds);
                          $this->seedCoupons();
                          $this->seedCredits($driverUserIds);

        $this->command->info('Dev data seeding complete.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cleanup
    // ─────────────────────────────────────────────────────────────────────────

    private function cleanStaleData(): void
    {
        // Remove anything accidentally seeded with parent_id=1 that isn't the real owner
        if ($this->ownerId !== 1) {
            foreach (['vehicles', 'vehicle_types', 'places', 'options'] as $table) {
                DB::table($table)->where('parent_id', 1)->delete();
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Vehicle Types
    // ─────────────────────────────────────────────────────────────────────────

    private function seedVehicleTypes(): array
    {
        $types = ['SUV', 'Berline', 'Hatchback', 'Minivan', 'Cabriolet'];
        $ids = [];
        foreach ($types as $type) {
            $vt = VehicleType::firstOrCreate(
                ['type' => $type, 'parent_id' => $this->ownerId]
            );
            $ids[] = $vt->id;
        }
        $this->command->info('  Vehicle types: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Places
    // ─────────────────────────────────────────────────────────────────────────

    private function seedPlaces(): array
    {
        $places = [
            ['name' => 'Casablanca Airport', 'city' => 'Casablanca', 'price' => 150],
            ['name' => 'Marrakech Centre',   'city' => 'Marrakech',  'price' => 0],
            ['name' => 'Rabat Gare',          'city' => 'Rabat',      'price' => 80],
            ['name' => 'Agadir Airport',      'city' => 'Agadir',     'price' => 200],
            ['name' => 'Fès Médina',          'city' => 'Fès',        'price' => 50],
        ];
        $ids = [];
        foreach ($places as $data) {
            $p = Place::firstOrCreate(
                ['name' => $data['name'], 'parent_id' => $this->ownerId],
                ['city' => $data['city'], 'price' => $data['price']]
            );
            $ids[] = $p->id;
        }
        $this->command->info('  Places: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Vehicles
    // ─────────────────────────────────────────────────────────────────────────

    private function seedVehicles(array $vtIds): array
    {
        $vehicles = [
            ['name' => 'Toyota RAV4',    'model' => '2023', 'type' => 0, 'engine' => 'Hybrid',  'plate' => 'A-1234-B', 'daily' => 350, 'seats' => 5, 'gear' => 'Auto',   'fuel' => 'Hybrid',  'km' => 12000],
            ['name' => 'Dacia Duster',   'model' => '2022', 'type' => 0, 'engine' => '1.5 dCi', 'plate' => 'B-5678-C', 'daily' => 220, 'seats' => 5, 'gear' => 'Manual', 'fuel' => 'Diesel',  'km' => 45000],
            ['name' => 'Renault Clio',   'model' => '2023', 'type' => 2, 'engine' => '1.0 TCe', 'plate' => 'C-9012-D', 'daily' => 180, 'seats' => 5, 'gear' => 'Manual', 'fuel' => 'Petrol',  'km' => 8000],
            ['name' => 'Mercedes GLE',   'model' => '2024', 'type' => 0, 'engine' => '3.0 V6',  'plate' => 'D-3456-E', 'daily' => 700, 'seats' => 5, 'gear' => 'Auto',   'fuel' => 'Diesel',  'km' => 5000],
            ['name' => 'Peugeot 208',    'model' => '2022', 'type' => 2, 'engine' => '1.2 PureTech', 'plate' => 'E-7890-F', 'daily' => 160, 'seats' => 5, 'gear' => 'Manual', 'fuel' => 'Petrol', 'km' => 30000],
            ['name' => 'Volkswagen T-Roc','model' => '2023', 'type' => 0, 'engine' => '1.5 TSI','plate' => 'F-2345-G', 'daily' => 380, 'seats' => 5, 'gear' => 'Auto',   'fuel' => 'Petrol',  'km' => 18000],
            ['name' => 'Ford Transit',   'model' => '2021', 'type' => 3, 'engine' => '2.0 EcoBlue', 'plate' => 'G-6789-H', 'daily' => 450, 'seats' => 9, 'gear' => 'Manual', 'fuel' => 'Diesel', 'km' => 60000],
        ];

        $yearCol = 'year_of_ﬁrst_immatriculation'; // Unicode ligature — matches DB column exactly
        $nextVid = (Vehicle::where('parent_id', $this->ownerId)->max('vehicle_id') ?? 0) + 1;

        $ids = [];
        foreach ($vehicles as $i => $v) {
            $vtId = $vtIds[$v['type']] ?? $vtIds[0];
            $existing = Vehicle::where('license_plate', $v['plate'])
                ->where('parent_id', $this->ownerId)->first();
            if (!$existing) {
                $existing = Vehicle::create([
                    'vehicle_id'               => $nextVid++,
                    'type'                     => $vtId,
                    'name'                     => $v['name'],
                    'model'                    => $v['model'],
                    'engine_type'              => $v['engine'],
                    'license_plate'            => $v['plate'],
                    'daily_rate'               => $v['daily'],
                    'number_of_seats'          => $v['seats'],
                    'gearbox'                  => $v['gear'],
                    'fuel_type'                => $v['fuel'],
                    'kilometers'               => $v['km'],
                    $yearCol                   => $v['model'],
                    'registration_expiry_date' => now()->addYears(2)->toDateString(),
                    'parent_id'                => $this->ownerId,
                ]);
            }
            $ids[] = $existing->id;
        }
        $this->command->info('  Vehicles: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Resolve existing driver user IDs
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveDriverUserIds(): array
    {
        $ids = Driver::where('parent_id', $this->ownerId)
            ->pluck('user_id')
            ->toArray();

        if (empty($ids)) {
            $this->command->warn('  No drivers found for owner. Run DatabaseSeeder first.');
        } else {
            $this->command->info('  Drivers resolved: ' . count($ids));
        }
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Expense Types
    // ─────────────────────────────────────────────────────────────────────────

    private function seedExpenseTypes(): array
    {
        $types = ['Carburant', 'Entretien', 'Assurance', 'Réparation', 'Nettoyage', 'Péage'];
        $ids = [];
        foreach ($types as $t) {
            $et = ExpenseType::firstOrCreate(
                ['title' => $t, 'parent_id' => $this->ownerId]
            );
            $ids[] = $et->id;
        }
        $this->command->info('  Expense types: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Inspection Types
    // ─────────────────────────────────────────────────────────────────────────

    private function seedInspectionTypes(): array
    {
        $types = ['Contrôle technique', 'Révision générale', 'Contrôle freins', 'Vidange'];
        $ids = [];
        foreach ($types as $t) {
            $it = InspectionType::firstOrCreate(
                ['type' => $t, 'parent_id' => $this->ownerId]
            );
            $ids[] = $it->id;
        }
        $this->command->info('  Inspection types: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reminder Types
    // ─────────────────────────────────────────────────────────────────────────

    private function seedReminderTypes(): array
    {
        $types = ['Renouvellement assurance', 'Vidange', 'Contrôle technique', 'Révision'];
        $ids = [];
        foreach ($types as $t) {
            $rt = ReminderType::firstOrCreate(
                ['type' => $t, 'parent_id' => $this->ownerId]
            );
            $ids[] = $rt->id;
        }
        $this->command->info('  Reminder types: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Addons
    // ─────────────────────────────────────────────────────────────────────────

    private function seedAddons(): array
    {
        $addons = [
            ['name' => 'GPS Navigation',     'price' => 50,  'billing_type' => 'per_day'],
            ['name' => 'Siège bébé',         'price' => 30,  'billing_type' => 'per_day'],
            ['name' => 'Conducteur additionnel', 'price' => 100, 'billing_type' => 'fixed'],
            ['name' => 'Assurance Premium',  'price' => 80,  'billing_type' => 'per_day'],
            ['name' => 'Wi-Fi portable',     'price' => 40,  'billing_type' => 'per_day'],
        ];
        $ids = [];
        foreach ($addons as $data) {
            $addon = Addon::firstOrCreate(
                ['name' => $data['name'], 'parent_id' => $this->ownerId],
                ['price' => $data['price'], 'billing_type' => $data['billing_type']]
            );
            $ids[] = $addon->id;
        }
        $this->command->info('  Addons: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Options
    // ─────────────────────────────────────────────────────────────────────────

    private function seedOptions(): void
    {
        $options = ['Climatisation', 'Bluetooth', 'Caméra de recul', 'Toit ouvrant', 'Apple CarPlay'];
        foreach ($options as $name) {
            Option::firstOrCreate(
                ['name' => $name, 'parent_id' => $this->ownerId]
            );
        }
        $this->command->info('  Options: ' . count($options));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bookings
    // ─────────────────────────────────────────────────────────────────────────

    private function seedBookings(array $vehicleIds, array $driverUserIds, array $placeIds, array $addonIds): array
    {
        if (empty($vehicleIds) || empty($driverUserIds) || empty($placeIds)) {
            $this->command->warn('  Skipping bookings — missing vehicles, drivers, or places.');
            return [];
        }

        $statuses = ['pending', 'approved', 'in_progress', 'completed', 'cancelled'];
        $payStatuses = ['paid', 'unpaid', 'partial'];
        $payMethods  = ['cash', 'stripe', 'paypal'];

        $bookingsData = [
            ['start' => '-30 days', 'end' => '-25 days', 'status' => 'completed', 'pay' => 'paid',    'method' => 'cash',   'v' => 0, 'd' => 0, 'pu' => 0, 'do' => 1],
            ['start' => '-20 days', 'end' => '-15 days', 'status' => 'completed', 'pay' => 'paid',    'method' => 'stripe', 'v' => 1, 'd' => 1, 'pu' => 1, 'do' => 2],
            ['start' => '-10 days', 'end' => '-5 days',  'status' => 'completed', 'pay' => 'partial', 'method' => 'cash',   'v' => 2, 'd' => 2, 'pu' => 0, 'do' => 0],
            ['start' => '-3 days',  'end' => '+2 days',  'status' => 'in_progress','pay' => 'paid',   'method' => 'cash',   'v' => 3, 'd' => 0, 'pu' => 1, 'do' => 3],
            ['start' => '+5 days',  'end' => '+10 days', 'status' => 'approved',  'pay' => 'unpaid',  'method' => 'cash',   'v' => 4, 'd' => 1, 'pu' => 2, 'do' => 4],
            ['start' => '+15 days', 'end' => '+20 days', 'status' => 'pending',   'pay' => 'unpaid',  'method' => 'paypal', 'v' => 0, 'd' => 2, 'pu' => 3, 'do' => 1],
            ['start' => '-50 days', 'end' => '-45 days', 'status' => 'completed', 'pay' => 'paid',    'method' => 'stripe', 'v' => 5, 'd' => 0, 'pu' => 0, 'do' => 2],
            ['start' => '-60 days', 'end' => '-58 days', 'status' => 'cancelled', 'pay' => 'unpaid',  'method' => 'cash',   'v' => 1, 'd' => 1, 'pu' => 2, 'do' => 3],
        ];

        $ids = [];
        $nextBookingId = (Booking::where('parent_id', $this->ownerId)->max('booking_id') ?? 0) + 1;

        foreach ($bookingsData as $data) {
            $vehicleId = $vehicleIds[$data['v'] % count($vehicleIds)];
            $driverUid = $driverUserIds[$data['d'] % count($driverUserIds)];
            $puId      = $placeIds[$data['pu'] % count($placeIds)];
            $doId      = $placeIds[$data['do'] % count($placeIds)];
            $vehicle   = Vehicle::find($vehicleId);
            $dailyRate = $vehicle ? $vehicle->daily_rate : 200;

            $start = Carbon::parse($data['start']);
            $end   = Carbon::parse($data['end']);
            $days  = max(1, $start->diffInDays($end));
            $amount = $dailyRate * $days;

            $addonStr = count($addonIds) >= 2
                ? implode(',', array_slice($addonIds, 0, 2))
                : ($addonIds[0] ?? null);

            $booking = Booking::create([
                'booking_id'      => $nextBookingId++,
                'vehicle'         => $vehicleId,
                'driver'          => $driverUid,
                'start_date'      => $start->toDateString(),
                'start_time'      => '09:00:00',
                'end_date'        => $end->toDateString(),
                'end_time'        => '18:00:00',
                'pickup_address'  => $puId,
                'drop_off_address'=> $doId,
                'status'          => $data['status'],
                'amount'          => $amount,
                'payment_status'  => $data['pay'],
                'payment_method'  => $data['method'],
                'addon'           => $addonStr,
                'daily_price_final'=> $dailyRate,
                'parent_id'       => $this->ownerId,
            ]);
            $ids[] = $booking->id;
        }
        $this->command->info('  Bookings: ' . count($ids));
        return $ids;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Booking Payments
    // ─────────────────────────────────────────────────────────────────────────

    private function seedBookingPayments(array $bookingIds): void
    {
        $methods = ['cash', 'stripe', 'paypal', 'bank_transfer'];
        foreach (array_slice($bookingIds, 0, 5) as $i => $bookingId) {
            $booking = Booking::find($bookingId);
            if (!$booking || $booking->payment_status === 'unpaid') continue;

            BookingPayment::firstOrCreate(
                ['booking_id' => $bookingId, 'parent_id' => $this->ownerId],
                [
                    'amount'  => $booking->payment_status === 'partial'
                        ? round($booking->amount * 0.5, 2)
                        : $booking->amount,
                    'payment_method' => $methods[$i % count($methods)],
                    'date'    => $booking->start_date,
                    'notes'   => 'Paiement enregistré lors de la prise en charge',
                ]
            );
        }
        $this->command->info('  Booking payments seeded.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Expenses
    // ─────────────────────────────────────────────────────────────────────────

    private function seedExpenses(array $vehicleIds, array $typeIds): void
    {
        if (empty($vehicleIds) || empty($typeIds)) return;

        $expenses = [
            ['title' => 'Plein carburant — Toyota RAV4',  'amount' => 450,  'v' => 0, 't' => 0, 'offset' => '-5 days'],
            ['title' => 'Révision 60 000 km — Duster',    'amount' => 1200, 'v' => 1, 't' => 1, 'offset' => '-15 days'],
            ['title' => 'Renouvellement assurance flotte', 'amount' => 8500, 'v' => 2, 't' => 2, 'offset' => '-30 days'],
            ['title' => 'Changement pneus — Clio',         'amount' => 1800, 'v' => 2, 't' => 3, 'offset' => '-20 days'],
            ['title' => 'Nettoyage intérieur complet',     'amount' => 250,  'v' => 3, 't' => 4, 'offset' => '-2 days'],
            ['title' => 'Péage autoroute — Rabat-Tanger',  'amount' => 85,   'v' => 4, 't' => 5, 'offset' => '-7 days'],
            ['title' => 'Réparation pare-brise — GLE',     'amount' => 2400, 'v' => 3, 't' => 3, 'offset' => '-45 days'],
        ];

        foreach ($expenses as $data) {
            $vid = $vehicleIds[$data['v'] % count($vehicleIds)];
            $tid = $typeIds[$data['t'] % count($typeIds)];
            Expense::firstOrCreate(
                ['title' => $data['title'], 'parent_id' => $this->ownerId],
                [
                    'vehicle'    => $vid,
                    'type'       => $tid,
                    'date'       => Carbon::parse($data['offset'])->toDateString(),
                    'amount'     => $data['amount'],
                ]
            );
        }
        $this->command->info('  Expenses: ' . count($expenses));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Inspections
    // ─────────────────────────────────────────────────────────────────────────

    private function seedInspections(array $vehicleIds, array $typeIds): void
    {
        if (empty($vehicleIds) || empty($typeIds)) return;

        $inspections = [
            ['v' => 0, 't' => 0, 'offset' => '-60 days', 'status' => 'pass',   'repair' => 'no_repair',  'meter' => 10000, 'amount' => 350],
            ['v' => 1, 't' => 1, 'offset' => '-30 days', 'status' => 'pass',   'repair' => 'no_repair',  'meter' => 44000, 'amount' => 800],
            ['v' => 2, 't' => 2, 'offset' => '-15 days', 'status' => 'fail',   'repair' => 'in_repair',  'meter' => 7500,  'amount' => 1500],
            ['v' => 3, 't' => 3, 'offset' => '-7 days',  'status' => 'pass',   'repair' => 'no_repair',  'meter' => 4800,  'amount' => 600],
            ['v' => 4, 't' => 0, 'offset' => '-45 days', 'status' => 'pass',   'repair' => 'repaired',   'meter' => 28000, 'amount' => 400],
        ];

        foreach ($inspections as $data) {
            $vid = $vehicleIds[$data['v'] % count($vehicleIds)];
            $idate = Carbon::parse($data['offset'])->toDateString();

            $exists = Inspection::where('vehicle', $vid)
                ->where('inspection_date', $idate)
                ->where('parent_id', $this->ownerId)
                ->exists();
            if ($exists) continue;

            Inspection::create([
                'vehicle'              => $vid,
                'inspector'            => 'Équipe technique',
                'inspection_date'      => $idate,
                'incoming_date'        => $idate,
                'meter_reading_incoming' => $data['meter'],
                'status'               => $data['status'],
                'repair_status'        => $data['repair'],
                'amount'               => $data['amount'],
                'notes'                => 'Inspection de routine — tout conforme.',
                'parent_id'            => $this->ownerId,
            ]);
        }
        $this->command->info('  Inspections: ' . count($inspections));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reminders
    // ─────────────────────────────────────────────────────────────────────────

    private function seedReminders(array $vehicleIds, array $remTypeIds): void
    {
        if (empty($vehicleIds) || empty($remTypeIds)) return;

        $reminders = [
            ['v' => 0, 't' => 0, 'offset' => '+30 days',  'status' => 'upcoming', 'name' => 'Renouvellement assurance RAV4'],
            ['v' => 1, 't' => 1, 'offset' => '+5 days',   'status' => 'urgent',   'name' => 'Vidange Duster'],
            ['v' => 2, 't' => 2, 'offset' => '-5 days',   'status' => 'overdue',  'name' => 'Contrôle technique Clio'],
            ['v' => 3, 't' => 3, 'offset' => '+60 days',  'status' => 'upcoming', 'name' => 'Révision GLE 10 000 km'],
            ['v' => 4, 't' => 0, 'offset' => '+90 days',  'status' => 'upcoming', 'name' => 'Renouvellement assurance Peugeot'],
            ['v' => 0, 't' => 1, 'offset' => '+10 days',  'status' => 'urgent',   'name' => 'Vidange RAV4'],
        ];

        foreach ($reminders as $data) {
            $vid = $vehicleIds[$data['v'] % count($vehicleIds)];
            $rtId = $remTypeIds[$data['t'] % count($remTypeIds)];
            Reminder::firstOrCreate(
                ['name' => $data['name'], 'parent_id' => $this->ownerId],
                [
                    'id_vehicle'      => $vid,
                    'reminder_type_id'=> $rtId,
                    'reminder_date'   => Carbon::parse($data['offset'])->toDateString(),
                    'status'          => $data['status'],
                    'note'            => 'Rappel automatique — à traiter avant l\'échéance.',
                ]
            );
        }
        $this->command->info('  Reminders: ' . count($reminders));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Rental Agreements
    // ─────────────────────────────────────────────────────────────────────────

    private function seedRentalAgreements(array $vehicleIds, array $driverUserIds): void
    {
        if (empty($vehicleIds) || empty($driverUserIds)) return;

        $agreements = [
            ['v' => 0, 'd' => 0, 'start' => '-30 days', 'end' => '-25 days', 'status' => 'completed'],
            ['v' => 1, 'd' => 1, 'start' => '-20 days', 'end' => '-15 days', 'status' => 'completed'],
            ['v' => 2, 'd' => 2, 'start' => '-3 days',  'end' => '+2 days',  'status' => 'active'],
            ['v' => 3, 'd' => 0, 'start' => '+5 days',  'end' => '+10 days', 'status' => 'pending'],
        ];

        $nextAgreementId = (RentalAgreement::where('parent_id', $this->ownerId)->max('agreement_id') ?? 0) + 1;

        foreach ($agreements as $data) {
            $vid  = $vehicleIds[$data['v'] % count($vehicleIds)];
            $duid = $driverUserIds[$data['d'] % count($driverUserIds)];
            $start = Carbon::parse($data['start']);
            $end   = Carbon::parse($data['end']);

            $exists = RentalAgreement::where('vehicle', $vid)
                ->where('rental_start_date', $start->toDateString())
                ->where('parent_id', $this->ownerId)
                ->exists();
            if ($exists) continue;

            RentalAgreement::create([
                'agreement_id'     => $nextAgreementId++,
                'date'             => $start->toDateString(),
                'rental_start_date'=> $start->toDateString(),
                'rental_end_date'  => $end->toDateString(),
                'rental_duration'  => $start->diffInDays($end),
                'vehicle'          => $vid,
                'driver'           => $duid,
                'status'           => $data['status'],
                'description'      => 'Contrat de location standard. Le véhicule est remis en bon état.',
                'terms_condition'  => "Le locataire s'engage à restituer le véhicule dans l'état où il l'a reçu.",
                'parent_id'        => $this->ownerId,
            ]);
        }
        $this->command->info('  Rental agreements: ' . count($agreements));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Coupons
    // ─────────────────────────────────────────────────────────────────────────

    private function seedCoupons(): void
    {
        $coupons = [
            ['name' => 'Bienvenue 10%',    'type' => 'percent', 'rate' => 10, 'code' => 'WELCOME10', 'limit' => 100],
            ['name' => 'Été 2026 — 20%',   'type' => 'percent', 'rate' => 20, 'code' => 'ETE2026',   'limit' => 50],
            ['name' => '-200 Dh fixe',     'type' => 'fixed',   'rate' => 200,'code' => 'FIXED200',  'limit' => 30],
            ['name' => 'Fidélité 15%',     'type' => 'percent', 'rate' => 15, 'code' => 'FIDELITE15','limit' => 200],
        ];

        foreach ($coupons as $data) {
            Coupon::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name'      => $data['name'],
                    'type'      => $data['type'],
                    'rate'      => $data['rate'],
                    'use_limit' => $data['limit'],
                    'status'    => 1,
                    'valid_for' => now()->addYear()->toDateString(),
                ]
            );
        }
        $this->command->info('  Coupons: ' . count($coupons));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Credits
    // ─────────────────────────────────────────────────────────────────────────

    private function seedCredits(array $driverUserIds): void
    {
        if (empty($driverUserIds)) return;

        $credits = [
            ['d' => 0, 'amount' => 500,  'status' => 'active',   'offset' => '-10 days'],
            ['d' => 1, 'amount' => 1200, 'status' => 'active',   'offset' => '-5 days'],
            ['d' => 2, 'amount' => 300,  'status' => 'inactive', 'offset' => '-20 days'],
        ];

        foreach ($credits as $data) {
            $duid = $driverUserIds[$data['d'] % count($driverUserIds)];
            Credit::firstOrCreate(
                ['driver_id' => $duid, 'parent_id' => $this->ownerId],
                [
                    'amount'      => $data['amount'],
                    'status'      => $data['status'],
                    'credit_date' => Carbon::parse($data['offset'])->toDateString(),
                ]
            );
        }
        $this->command->info('  Credits: ' . count($credits));
    }
}
