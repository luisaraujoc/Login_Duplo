<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fleet vehicles ("veiculos"). Global, not scoped to a ledger account —
     * matches the legacy schema, which has no idconta on this table.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate');
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('manufacture_year');
            $table->unsignedSmallInteger('model_year');
            $table->string('renavam')->nullable();
            $table->string('chassis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
