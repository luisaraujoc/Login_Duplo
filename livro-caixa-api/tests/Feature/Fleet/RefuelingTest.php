<?php

use App\Models\FuelProduct;
use App\Models\FuelSupplier;
use App\Models\Refueling;
use App\Models\User;
use App\Models\Vehicle;

it('creates a refueling and defaults the target efficiency to 14.10 when omitted', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();
    $supplier = FuelSupplier::factory()->create();
    $product = FuelProduct::factory()->create();

    $this->postJson('/api/refuelings', [
        'vehicle_id' => $vehicle->id,
        'fuel_supplier_id' => $supplier->id,
        'fuel_product_id' => $product->id,
        'invoice_number' => '1001',
        'refueled_at' => '2026-07-01 08:00:00',
        'odometer_km' => 10000,
        'quantity' => 40,
        'unit_price' => 5.79,
    ])->assertCreated()
        ->assertJsonPath('data.total_cost', 231.6)
        ->assertJsonPath('data.target_efficiency_km_per_liter', 14.1);
});

it('creates a refueling with an explicit target efficiency', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    $this->postJson('/api/refuelings', [
        'vehicle_id' => $vehicle->id,
        'fuel_supplier_id' => FuelSupplier::factory()->create()->id,
        'fuel_product_id' => FuelProduct::factory()->create()->id,
        'invoice_number' => '1002',
        'refueled_at' => '2026-07-01 08:00:00',
        'odometer_km' => 10000,
        'quantity' => 40,
        'unit_price' => 5.79,
        'target_efficiency_km_per_liter' => 12,
    ])->assertCreated()->assertJsonPath('data.target_efficiency_km_per_liter', 12);
});

it('rejects a refueling with missing required fields', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/refuelings', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'vehicle_id', 'fuel_supplier_id', 'fuel_product_id',
            'invoice_number', 'refueled_at', 'odometer_km', 'quantity', 'unit_price',
        ]);
});

it('lists refuelings filtered by vehicle', function () {
    $this->actingAs(User::factory()->create());
    $vehicleA = Vehicle::factory()->create();
    $vehicleB = Vehicle::factory()->create();

    Refueling::factory()->count(2)->create(['vehicle_id' => $vehicleA->id]);
    Refueling::factory()->create(['vehicle_id' => $vehicleB->id]);

    $this->getJson("/api/refuelings?vehicle_id={$vehicleA->id}")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('updates a refueling', function () {
    $this->actingAs(User::factory()->create());
    $refueling = Refueling::factory()->create(['odometer_km' => 1000]);

    $this->putJson("/api/refuelings/{$refueling->id}", [
        'vehicle_id' => $refueling->vehicle_id,
        'fuel_supplier_id' => $refueling->fuel_supplier_id,
        'fuel_product_id' => $refueling->fuel_product_id,
        'invoice_number' => $refueling->invoice_number,
        'refueled_at' => $refueling->refueled_at->toDateTimeString(),
        'odometer_km' => 1500,
        'quantity' => $refueling->quantity,
        'unit_price' => $refueling->unit_price,
    ])->assertOk()->assertJsonPath('data.odometer_km', 1500);
});

it('deletes a refueling', function () {
    $this->actingAs(User::factory()->create());
    $refueling = Refueling::factory()->create();

    $this->deleteJson("/api/refuelings/{$refueling->id}")->assertNoContent();

    expect(Refueling::query()->find($refueling->id))->toBeNull();
});

it('blocks refueling routes for a guest', function () {
    $this->getJson('/api/refuelings')->assertUnauthorized();
});
