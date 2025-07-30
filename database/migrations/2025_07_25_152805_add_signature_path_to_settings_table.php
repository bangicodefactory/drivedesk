<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


/**
 * Class AddSignaturePathToSettingsTable
 *
 * This migration adds a 'signature_path' column to the 'settings' table.
 * The column is nullable and will be placed after the 'value' column.ss
 */
class AddSignaturePathToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('value');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });
    }
}
