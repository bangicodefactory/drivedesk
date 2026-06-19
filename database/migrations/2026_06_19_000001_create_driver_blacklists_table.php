<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver blacklist (BAN-252). A driver (the driver's users.id) can be flagged so
 * staff are warned before booking/contracting them. Reversible — a null
 * `lifted_at` means "currently blacklisted"; lifting keeps the row for history.
 *
 * Additive-only new table (approved CLAUDE.md §4 exception) — touches no existing
 * table. `overrides` is an append-only JSON audit of "proceed anyway" decisions
 * (who/when/which booking or contract), so the feature needs no second table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_blacklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('driver_user_id'); // the driver's users.id
            $table->unsignedBigInteger('parent_id');      // tenant (owner) id
            $table->text('reason');
            $table->unsignedBigInteger('blacklisted_by')->nullable();
            $table->timestamp('lifted_at')->nullable();   // null = active
            $table->unsignedBigInteger('lifted_by')->nullable();
            $table->json('overrides')->nullable();
            $table->timestamps();

            // "Is this driver actively blacklisted for this tenant?"
            $table->index(['parent_id', 'driver_user_id', 'lifted_at'], 'driver_blacklists_active_idx');
            $table->index('driver_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_blacklists');
    }
};
