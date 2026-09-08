<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Additive, nullable columns for the /reserve booking wizard's fuller
        // customer-details step. Existing callers (CarDetails.jsx's simpler
        // form) don't send these — they stay optional so that path is unaffected.
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('vehicle_details');
            $table->string('nationality', 80)->nullable()->after('age');
            $table->unsignedTinyInteger('driving_experience')->nullable()->after('nationality');
            $table->unsignedTinyInteger('passengers')->nullable()->after('driving_experience');
            $table->string('whatsapp', 30)->nullable()->after('passengers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn(['age', 'nationality', 'driving_experience', 'passengers', 'whatsapp']);
        });
    }
};
