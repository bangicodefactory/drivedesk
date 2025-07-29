<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TvaSeeder extends Seeder
{
    public function run()
    {
        $records = [];

        for ($i = 1; $i <= 50; $i++) {  // insert 50 records
            $date = Carbon::now()->subDays(rand(0, 365)); // random date in last year

            $records[] = [
                'parent_id' => null,
                'booking_id' => rand(1, 100),
                'facture_number' => 'INV-' . Str::upper(Str::random(6)) . $i,
                'facture_date' => $date->format('Y-m-d'),
                'reference' => 'Ref-' . Str::random(5),
                'client_name' => 'Client ' . $i,
                'client_address' => '123 Client Street, City, Country',
                'company_name' => 'DIRECT ONDERWEG',
                'company_address' => '456 Company Ave, City, Country',
                'month' => $date->month,
                'year' => $date->year,
                'total_amount' => rand(1000, 10000),
                'tva_amount' => rand(100, 1000),
                'status' => 1,
                'generated_date' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'designation' => 'Designation ' . $i,
                'quantity' => rand(1, 10),
                'unit_price_ht' => rand(100, 500),
                'total_ht' => rand(1000, 5000),
                'tva' => rand(100, 500),
                'montant_ttc' => rand(1100, 5500),
                'ice_number' => 'ICE' . rand(10000, 99999),
                'rc_number' => 'RC' . rand(10000, 99999),
                'tp_number' => 'TP' . rand(10000, 99999),
                'nif_number' => 'NIF' . rand(10000, 99999),
                'deleted_at' => null,
            ];
        }

        DB::table('tvas')->insert($records);
    }
}
