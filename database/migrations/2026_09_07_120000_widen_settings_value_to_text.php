<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BAN-311: `settings.value` shipped as VARCHAR(255).
 *
 * That was enough while settings held company names, currency symbols and
 * colour codes. It is not enough for `rental_agreement_terms`, the contract
 * text a deployment prints on every rental agreement -- drivedesk's own is 1746
 * characters. With `strict` on (config/database.php) the save raises
 * SQLSTATE[22001]; with it off the value is silently truncated and a cut-off
 * contract prints. `rental_agreements.terms_condition` is already `text`, so
 * this only brings the settings table in line with where the same content
 * already lives.
 *
 * Additive and backward-compatible per CLAUDE.md 4: TEXT holds everything
 * VARCHAR(255) held, no existing row changes, no backfill.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('value')->change();
        });
    }

    /**
     * Reversible, with one caveat worth stating rather than hiding: any row
     * written after `up()` that exceeds 255 characters cannot fit the narrower
     * column, so the database will refuse or truncate it. Shorten
     * `rental_agreement_terms` before rolling back.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }
};
