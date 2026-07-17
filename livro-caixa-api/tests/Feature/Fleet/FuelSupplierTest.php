<?php

use App\Models\FuelSupplier;
use App\Models\User;

it('lists fuel suppliers', function () {
    $this->actingAs(User::factory()->create());
    FuelSupplier::factory()->count(2)->create();

    $this->getJson('/api/fuel-suppliers')->assertOk()->assertJsonCount(2, 'data');
});

it('creates a fuel supplier', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/fuel-suppliers', [
        'company_name' => 'Posto Ipiranga Centro',
        'address' => 'Rua A, 100',
        'neighborhood' => 'Centro',
        'city' => 'Salvador',
        'state' => 'BA',
        'zip_code' => '40000000',
        'phone' => '7130000000',
        'cnpj' => '12345678000199',
    ])->assertCreated()->assertJsonPath('data.company_name', 'Posto Ipiranga Centro');

    expect(FuelSupplier::query()->where('company_name', 'Posto Ipiranga Centro')->exists())->toBeTrue();
});

it('rejects a fuel supplier with missing fields', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/fuel-suppliers', [])->assertUnprocessable();
});

it('updates a fuel supplier', function () {
    $this->actingAs(User::factory()->create());
    $supplier = FuelSupplier::factory()->create();

    $this->putJson("/api/fuel-suppliers/{$supplier->id}", [
        ...$supplier->only(['address', 'neighborhood', 'city', 'state', 'zip_code', 'phone', 'cnpj']),
        'company_name' => 'Novo Nome',
    ])->assertOk()->assertJsonPath('data.company_name', 'Novo Nome');
});

it('deletes a fuel supplier', function () {
    $this->actingAs(User::factory()->create());
    $supplier = FuelSupplier::factory()->create();

    $this->deleteJson("/api/fuel-suppliers/{$supplier->id}")->assertNoContent();

    expect(FuelSupplier::query()->find($supplier->id))->toBeNull();
});

it('blocks fuel supplier routes for a guest', function () {
    $this->getJson('/api/fuel-suppliers')->assertUnauthorized();
});
