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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_vehicle')->unique();
            $table->string('name', 191);
            $table->text('note')->nullable();
            $table->date('reminder_date');
            $table->string('status', 191);
            $table->foreignId('reminder_type_id')->constrained('reminder_types');
            $table->foreign('id_vehicle')->references('id')->on('vehicles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
