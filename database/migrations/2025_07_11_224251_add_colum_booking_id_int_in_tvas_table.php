<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // booking_id is an integer that can be null, added to the tvas table
    public function up(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            //
            $table->integer('booking_id')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            //
            $table->dropColumn('booking_id');
        });
    }
};
