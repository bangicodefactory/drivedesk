<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tvas', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_amount');
            $table->integer('tva_amount');
            $table->string('status');
            $table->timestamp('generated_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvas');
    }
};
