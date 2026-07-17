<?php

use App\Models\FuelProduct;
use App\Models\User;

it('lists fuel products', function () {
    $this->actingAs(User::factory()->create());
    FuelProduct::factory()->count(2)->create();

    $this->getJson('/api/fuel-products')->assertOk()->assertJsonCount(2, 'data');
});

it('creates a fuel product', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/fuel-products', [
        'code' => '1',
        'name' => 'Gasolina Comum',
        'unit' => 'L',
    ])->assertCreated()->assertJsonPath('data.name', 'Gasolina Comum');

    expect(FuelProduct::query()->where('code', '1')->exists())->toBeTrue();
});

it('rejects a fuel product with missing fields', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/fuel-products', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'name', 'unit']);
});

it('updates a fuel product', function () {
    $this->actingAs(User::factory()->create());
    $product = FuelProduct::factory()->create();

    $this->putJson("/api/fuel-products/{$product->id}", [
        'code' => $product->code,
        'name' => 'Etanol Premium',
        'unit' => $product->unit,
    ])->assertOk()->assertJsonPath('data.name', 'Etanol Premium');
});

it('deletes a fuel product', function () {
    $this->actingAs(User::factory()->create());
    $product = FuelProduct::factory()->create();

    $this->deleteJson("/api/fuel-products/{$product->id}")->assertNoContent();

    expect(FuelProduct::query()->find($product->id))->toBeNull();
});

it('blocks fuel product routes for a guest', function () {
    $this->getJson('/api/fuel-products')->assertUnauthorized();
});
