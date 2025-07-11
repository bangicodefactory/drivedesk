<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement('ALTER TABLE tvas MODIFY month INT NULL');
        DB::statement('ALTER TABLE tvas MODIFY year INT NULL');
        DB::statement('ALTER TABLE tvas MODIFY total_amount INT NULL');
        DB::statement('ALTER TABLE tvas MODIFY tva_amount INT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement('ALTER TABLE tvas MODIFY month INT NOT NULL');
        DB::statement('ALTER TABLE tvas MODIFY year INT NOT NULL');
        DB::statement('ALTER TABLE tvas MODIFY total_amount INT NOT NULL');
        DB::statement('ALTER TABLE tvas MODIFY tva_amount INT NOT NULL');
    }
};
