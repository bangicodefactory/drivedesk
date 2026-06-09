<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes — perf-audit findings F-15 / F-16 / F-17
 * (see docs/perf-audit.md, "Database Index Audit — 2026-06-09").
 *
 * The app is multi-tenant: every list/report query is scoped by `parent_id`,
 * but `parent_id` was unindexed on every major table, forcing full table scans
 * (EXPLAIN: TVA report scanned 10,592 rows; bookings list scanned 1,364 +
 * filesort). These composite indexes lead with `parent_id` to match the real
 * access pattern (tenant scope + the usual ORDER BY created_at / status
 * filter), plus a few missing foreign-key indexes.
 *
 * Additive only — no column or data changes. `ADD INDEX` runs as online DDL on
 * MySQL 5.7+/8 at these table sizes; schedule a maintenance window if a future
 * table grows very large.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            $table->index(['parent_id', 'year', 'month'], 'tvas_parent_period_idx'); // F-16
            $table->index('booking_id', 'tvas_booking_idx');                         // F-17
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['parent_id', 'created_at'], 'bookings_parent_created_idx'); // F-15 (removes filesort)
            $table->index(['parent_id', 'status'], 'bookings_parent_status_idx');
        });

        Schema::table('rental_agreements', function (Blueprint $table) {
            $table->index(['parent_id', 'created_at'], 'ra_parent_created_idx');
        });

        Schema::table('booking_payments', function (Blueprint $table) {
            $table->index('parent_id', 'bp_parent_idx');
            $table->index('booking_id', 'bp_booking_idx'); // F-17
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->index('parent_id', 'drivers_parent_idx');
            $table->index('user_id', 'drivers_user_idx'); // F-17
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('parent_id', 'vehicles_parent_idx');
        });

        Schema::table('credits', function (Blueprint $table) {
            $table->index('parent_id', 'credits_parent_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('parent_id', 'expenses_parent_idx');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->index('parent_id', 'inspections_parent_idx');
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->index('parent_id', 'reminders_parent_idx');
        });

        Schema::table('logged_histories', function (Blueprint $table) {
            $table->index(['parent_id', 'created_at'], 'lh_parent_created_idx'); // F-17 / F-19
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('parent_id', 'users_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            $table->dropIndex('tvas_parent_period_idx');
            $table->dropIndex('tvas_booking_idx');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_parent_created_idx');
            $table->dropIndex('bookings_parent_status_idx');
        });
        Schema::table('rental_agreements', function (Blueprint $table) {
            $table->dropIndex('ra_parent_created_idx');
        });
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropIndex('bp_parent_idx');
            $table->dropIndex('bp_booking_idx');
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex('drivers_parent_idx');
            $table->dropIndex('drivers_user_idx');
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_parent_idx');
        });
        Schema::table('credits', function (Blueprint $table) {
            $table->dropIndex('credits_parent_idx');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_parent_idx');
        });
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex('inspections_parent_idx');
        });
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropIndex('reminders_parent_idx');
        });
        Schema::table('logged_histories', function (Blueprint $table) {
            $table->dropIndex('lh_parent_created_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_parent_idx');
        });
    }
};
