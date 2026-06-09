<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * perf-audit F-19 follow-up: the nightly logged_histories prune filters by
 * `created_at` alone (`DELETE WHERE created_at <= ?`). The existing
 * `lh_parent_created_idx (parent_id, created_at)` leads with `parent_id`, so it
 * can't serve a created_at-only predicate — the prune would full-scan. Add a
 * standalone `created_at` index so the prune is a range scan.
 *
 * Additive only. `ADD INDEX` is online DDL on MySQL 5.7+/8.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logged_histories', function (Blueprint $table) {
            $table->index('created_at', 'lh_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('logged_histories', function (Blueprint $table) {
            $table->dropIndex('lh_created_at_idx');
        });
    }
};
