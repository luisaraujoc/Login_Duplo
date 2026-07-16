<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fuel types ("produtos"), e.g. gasoline, ethanol, diesel — global catalog.
     */
    public function up(): void
    {
        Schema::create('fuel_products', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('unit', 5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_products');
    }
};
