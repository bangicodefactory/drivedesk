<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id')->default(0);
            $table->integer('type')->default(0);
            $table->string('name')->nullable();
            $table->string('model')->nullable();
            $table->string('engine_type')->nullable();
            $table->string('engine_no')->nullable();
            $table->date('registration_expiry_date')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('document')->nullable();
            $table->float('daily_rate')->default(0);
            $table->year('year_of_ﬁrst_immatriculation')->default(0);
            $table->string('gearbox')->default(0);
            $table->string('fuel_type')->default(0);
            $table->integer('number_of_seats')->nullable();
            $table->string('kilometers')->nullable();
            $table->string('option')->nullable();
            $table->text('notes')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
