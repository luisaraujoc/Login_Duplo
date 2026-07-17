<?php

use App\Enums\CashFlowType;
use App\Models\Category;
use App\Models\Movement;

it('casts a category type column to the CashFlowType enum', function () {
    $category = Category::factory()->credit()->create();

    expect($category->type)->toBeInstanceOf(CashFlowType::class);
    expect($category->type)->toBe(CashFlowType::Credit);
});

it('casts a movement type column to the CashFlowType enum', function () {
    $movement = Movement::factory()->debit()->create();

    expect($movement->type)->toBeInstanceOf(CashFlowType::class);
    expect($movement->type)->toBe(CashFlowType::Debit);
});

it('has exactly two cash flow types: credit and debit', function () {
    expect(array_map(fn (CashFlowType $c) => $c->value, CashFlowType::cases()))
        ->toBe(['credit', 'debit']);
});
