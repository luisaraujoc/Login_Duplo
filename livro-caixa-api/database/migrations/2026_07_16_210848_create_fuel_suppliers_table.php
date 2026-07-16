<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fuel suppliers ("fornecedores") — global catalog, as in the legacy
     * schema comment: "lista de fornecedores de combustiveis".
     */
    public function up(): void
    {
        Schema::create('fuel_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('address');
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code', 8);
            $table->string('phone');
            $table->string('cnpj', 14);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_suppliers');
    }
};
