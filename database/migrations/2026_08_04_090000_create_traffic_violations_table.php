<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traffic violations (BAN-260). A violation notice arrives with a plate and an
 * instant, but no driver; the app matches it back to the booking that held the
 * vehicle at that moment and to the renter who signed it.
 *
 * Additive-only new table (approved CLAUDE.md §4 exception) — touches no
 * existing table and adds no column or index to one.
 *
 * Two host-specific choices, both deliberate:
 *
 *  1. `engine = InnoDB` is set explicitly. The production MariaDB host defaults
 *     to MyISAM, which silently drops constraints (the fix already applied in
 *     PR #199 for the CI/prod engine mismatch).
 *
 *  2. There are NO foreign-key constraints to bookings/vehicles/users. Those
 *     tables are still MyISAM on the directonderweg deploy, and an InnoDB ->
 *     MyISAM foreign key fails outright. Plain unsignedBigInteger + index is
 *     the same shape driver_blacklists uses, for the same reason.
 *
 * `unique(parent_id, reference)` is what makes re-importing the same authority
 * file idempotent. MySQL/MariaDB allow many NULLs in a unique index, so rows
 * entered by hand without a notice number are unaffected — but the importer and
 * the controller must normalize a blank reference to NULL, never ''.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_violations', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id');
            $table->integer('parent_id')->default(0);   // tenant (owner) id

            // --- The notice, as issued -------------------------------------
            $table->string('reference')->nullable();    // authority notice number
            $table->string('authority')->nullable();    // issuing body
            $table->string('license_plate');            // raw, exactly as printed
            $table->dateTime('occurred_at');            // the violation instant
            $table->date('notice_date')->nullable();    // when the owner received it
            $table->string('location')->nullable();
            $table->text('description')->nullable();    // the offence
            $table->decimal('amount', 10, 2)->default(0);

            // --- The match --------------------------------------------------
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            // Snapshot of the renter at match time: bookings are edited in
            // place, so the link alone would not survive a later change.
            $table->unsignedBigInteger('driver_user_id')->nullable();
            $table->string('match_confidence')->default('none'); // exact|probable|none
            $table->string('match_source')->nullable();          // auto|manual
            $table->timestamp('matched_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            // --- Recovery tracking -------------------------------------------
            $table->string('status')->default('new');            // new|notified|paid|contested|written_off
            $table->string('liable_party')->default('unknown');  // renter|company|unknown
            $table->decimal('amount_recovered', 10, 2)->default(0);

            $table->string('document')->nullable();   // scan of the notice
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'occurred_at'], 'traffic_violations_parent_occurred_idx');
            $table->index(['parent_id', 'status'], 'traffic_violations_parent_status_idx');
            $table->index(['parent_id', 'booking_id'], 'traffic_violations_parent_booking_idx');
            $table->index(['parent_id', 'driver_user_id'], 'traffic_violations_parent_driver_idx');
            $table->unique(['parent_id', 'reference'], 'traffic_violations_parent_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_violations');
    }
};
