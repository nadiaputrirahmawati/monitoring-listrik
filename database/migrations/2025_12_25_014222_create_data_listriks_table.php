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
        Schema::create('data_listriks', function (Blueprint $table) {
            $table->id();
            $table->float('tegangan')->nullable();      // Volt
            $table->float('arus')->nullable();          // Ampere
            $table->float('watt')->nullable();          // Watt
            $table->decimal('energi_kwh', 15, 6)->nullable();    // kWh
            $table->decimal('biaya', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_listriks');
    }
};
