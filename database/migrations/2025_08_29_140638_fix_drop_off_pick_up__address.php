<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropColumn(['pickup_address', 'drop_off_address']);
        });

        Schema::table('booking_requests', function (Blueprint $table) {
 
            $table->unsignedBigInteger('pickup_address')->after('end_time');
            $table->unsignedBigInteger('drop_off_address')->after('pickup_address');

            $table->foreign('pickup_address')
                  ->references('id')
                  ->on('places')
                  ->onDelete('restrict');

            $table->foreign('drop_off_address')
                  ->references('id')
                  ->on('places')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['pickup_address']);
            $table->dropForeign(['drop_off_address']);

            // Drop the columns
            $table->dropColumn(['pickup_address', 'drop_off_address']);

            // Restore as plain integers
            $table->integer('pickup_address')->default(0);
            $table->integer('drop_off_address')->default(0);
        });
    }
};
