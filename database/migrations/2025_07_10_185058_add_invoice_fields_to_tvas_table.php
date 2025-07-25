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
        Schema::table('tvas', function (Blueprint $table) {
            // Invoice identification
            $table->string('facture_number')->nullable()->after('id');
            $table->date('facture_date')->nullable()->after('facture_number');
            $table->string('reference')->nullable()->after('facture_date');
            
            // Client information
            $table->string('client_name')->nullable()->after('reference');
            $table->text('client_address')->nullable()->after('client_name');
            
            // Company information
            $table->string('company_name')->default('DIRECT ONDERWEG')->after('client_address');
            $table->text('company_address')->nullable()->after('company_name');
            
            // Product/Service details
            $table->string('designation')->nullable();
            $table->decimal('quantity', 10, 2)->nullable()->after('designation');
            $table->decimal('unit_price_ht', 10, 2)->nullable()->after('quantity'); // P.U.H.T
            $table->decimal('total_ht', 10, 2)->nullable()->after('unit_price_ht'); // TOTAL H.T

            // Tax calculations
            $table->decimal('tva', 10, 2)->nullable()->after('total_ht'); // TVA percentage
            $table->decimal('montant_ttc', 10, 2)->nullable()->after('tva');
            
            // Company registration details
            $table->string('ice_number')->nullable()->after('montant_ttc');
            $table->string('rc_number')->nullable()->after('ice_number');
            $table->string('tp_number')->nullable()->after('rc_number');
            $table->string('nif_number')->nullable()->after('tp_number');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tvas', function (Blueprint $table) {
            // Remove invoice fields
            $table->dropColumn([
                'facture_number',
                'facture_date',
                'reference',
                'client_name',
                'client_address',
                'company_name',
                'company_address',
                'designation',
                'quantity',
                'unit_price_ht',
                'total_ht',
                'montant_ht',
                'tva_rate',
                'montant_ttc',
                'ice_number',
                'rc_number',
                'tp_number',
                'nif_number',
                
            ]);
        });
    }
};