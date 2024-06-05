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
        Schema::create("RefGate", function (Blueprint $table) {
            $table->id();
            $table->string('CodeGate');
            $table->string('LocationCode');
            $table->string('VihiclePlate')->default(null);
            $table->string('Duration')->default(null);
            $table->dateTime('InTime')->default(null);
            $table->dateTime('OutTime')->default(null);
            $table->integer('Status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RefGate');
    }
};
