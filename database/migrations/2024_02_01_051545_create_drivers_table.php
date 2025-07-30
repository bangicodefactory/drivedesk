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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->integer('driver_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->string('gender')->nullable();
            $table->integer('age')->default(0);
            $table->text('address')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('license_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('document')->nullable();
            $table->string('license')->nullable();
            $table->string('reference')->nullable();
            $table->integer('parent_id')->default(0);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('drivers');
    }
};
