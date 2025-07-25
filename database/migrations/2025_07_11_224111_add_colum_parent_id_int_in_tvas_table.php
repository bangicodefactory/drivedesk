<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    //parent_id is an integer that can be null, added to the tvas table test
    public function up(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            //
            $table->integer('parent_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            //
            $table->dropColumn('parent_id');
        });
    }
};
