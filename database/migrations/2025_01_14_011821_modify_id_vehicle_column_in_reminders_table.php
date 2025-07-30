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
        Schema::table('reminders', function (Blueprint $table) {
                        // First drop the foreign key constraint
                        $table->dropForeign('reminders_id_vehicle_foreign');
            
                        // Then drop the unique index
                        $table->dropUnique('reminders_id_vehicle_unique');
                        
                        // Recreate the foreign key without unique constraint
                        $table->foreign('id_vehicle')
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
        Schema::table('reminders', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign('reminders_id_vehicle_foreign');
            
            // Recreate unique index
            $table->unique('id_vehicle');
            
            // Recreate the foreign key
            $table->foreign('id_vehicle')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');

        });
    }
};
