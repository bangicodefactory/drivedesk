<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            // Per-payment invoice day-count (the Qty printed on the facture).
            // Nullable: legacy rows and payments with no explicit/apportioned
            // days derive it proportionally at facture time. Persisting it lets
            // deferred invoicing (flushed once a booking is fully paid) reproduce
            // the exact days computed when the payment was recorded — a manual
            // override, or a cash-split receipt's apportioned share.
            $table->unsignedInteger('invoice_days')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn('invoice_days');
        });
    }
};
