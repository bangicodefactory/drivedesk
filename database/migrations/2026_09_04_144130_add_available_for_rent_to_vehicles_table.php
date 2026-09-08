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
        // Default true: every existing vehicle keeps showing on the public
        // storefront exactly as it does today — no backfill needed to stay
        // correct (CLAUDE.md §4).
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('available_for_rent')->default(true)->after('kilometers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('available_for_rent');
        });
    }
};
