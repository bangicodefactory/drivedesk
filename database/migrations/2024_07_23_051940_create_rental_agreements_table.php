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
        Schema::create('rental_agreements', function (Blueprint $table) {
            $table->id();
            $table->integer('agreement_id')->default(0);
            $table->date('date')->nullable();
            $table->date('rental_start_date')->nullable();
            $table->date('rental_end_date')->nullable();
            $table->integer('rental_duration')->default(0);
            $table->integer('driver')->default(0);
            $table->integer('vehicle')->default(0);
            $table->text('terms_condition')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('rental_agreements');
    }
};
