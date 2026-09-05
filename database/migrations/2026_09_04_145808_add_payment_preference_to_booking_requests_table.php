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
        // Nullable: CarDetails.jsx's simpler booking form doesn't collect this,
        // so existing/other callers of storeBooking() are unaffected. No real
        // gateway charge happens for 'paypal'/'cmi' yet (neither is integrated
        // anywhere in this codebase) — it just records what the customer asked
        // for so staff can follow up.
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->string('payment_preference', 20)->nullable()->after('whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn('payment_preference');
        });
    }
};
