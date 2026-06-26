<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// IST-229: DB backstop for the app-level duplicate-plate guard. A vehicle's
// license_plate must be unique PER TENANT (parent_id). Two different tenants may
// legitimately hold the same plate, so the index is composite. Case-insensitivity
// comes from the column's collation (utf8mb4_*_ci); the controller trims/normalises
// on save. Multiple NULL plates remain allowed (MySQL ignores NULLs in unique
// indexes), matching the column's nullable definition.
//
// Prerequisite: the table must be free of (parent_id, license_plate) duplicates
// or this fails — prod (directonderweg) was de-duplicated before this shipped.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique(['parent_id', 'license_plate'], 'vehicles_parent_plate_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_parent_plate_unique');
        });
    }
};
