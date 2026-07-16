<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refueling events ("abastecimentos") — the transactional core of the
     * fleet module. total_cost mirrors the legacy valorcompra generated
     * column (quantity * unit_price); computed in the model rather than as
     * a DB-generated column, to stay portable across database engines.
     * Deliberately has no link to movements: refueling does not post a
     * cash movement in this system.
     */
    public function up(): void
    {
        Schema::create('refuelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('fuel_supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('fuel_product_id')->constrained()->restrictOnDelete();
            $table->foreignId('nfe_link_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->string('access_key', 60)->nullable();
            $table->dateTime('refueled_at');
            $table->unsignedInteger('odometer_km');
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 10, 4);
            $table->string('notes')->nullable();
            $table->decimal('target_efficiency_km_per_liter', 10, 2)->default(14.10);
            $table->timestamps();

            $table->index(['vehicle_id', 'odometer_km']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refuelings');
    }
};
