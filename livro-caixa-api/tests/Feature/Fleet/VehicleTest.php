<?php

use App\Models\User;
use App\Models\Vehicle;

it('lists vehicles (global, no account needed)', function () {
    $this->actingAs(User::factory()->create());
    Vehicle::factory()->count(2)->create();

    $this->getJson('/api/vehicles')->assertOk()->assertJsonCount(2, 'data');
});

it('creates a vehicle', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/vehicles', [
        'plate' => 'ABC1D23',
        'brand' => 'Fiat',
        'model' => 'Strada',
        'manufacture_year' => 2020,
        'model_year' => 2021,
        'renavam' => '12345678900',
        'chassis' => '9BD12345678901234',
    ])->assertCreated()->assertJsonPath('data.plate', 'ABC1D23');

    expect(Vehicle::query()->where('plate', 'ABC1D23')->exists())->toBeTrue();
});

it('rejects a vehicle with missing required fields', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/vehicles', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['plate', 'brand', 'model', 'manufacture_year', 'model_year']);
});

it('updates a vehicle', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create(['plate' => 'OLD0001']);

    $this->putJson("/api/vehicles/{$vehicle->id}", [
        'plate' => 'NEW0001',
        'brand' => $vehicle->brand,
        'model' => $vehicle->model,
        'manufacture_year' => $vehicle->manufacture_year,
        'model_year' => $vehicle->model_year,
    ])->assertOk()->assertJsonPath('data.plate', 'NEW0001');
});

it('deletes a vehicle', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    $this->deleteJson("/api/vehicles/{$vehicle->id}")->assertNoContent();

    expect(Vehicle::query()->find($vehicle->id))->toBeNull();
});

it('blocks vehicle routes for a guest', function () {
    $this->getJson('/api/vehicles')->assertUnauthorized();
});
