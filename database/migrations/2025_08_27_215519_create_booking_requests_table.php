<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->default(0);

            // Foreign keys
            $table->unsignedBigInteger('vehicle'); // reference to vehicles
            $table->unsignedBigInteger('driver');  // reference to guests

            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('pickup_address')->default(0);
            $table->integer('drop_off_address')->default(0);
            $table->string('status')->nullable();
            $table->integer('amount')->default(0);
            $table->string('payment_status')->nullable();
            $table->text('payment_notes')->nullable();
            $table->text('notes')->nullable();
            $table->string('addon')->nullable();
            $table->text('details')->nullable();
            $table->text('vehicle_details')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('driver')
                ->references('id')
                ->on('guests')
                ->onDelete('cascade');

            $table->foreign('vehicle')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
