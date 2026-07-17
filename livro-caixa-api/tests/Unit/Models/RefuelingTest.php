<?php

use App\Models\Refueling;

it('computes total_cost as quantity times unit_price, rounded to 2 decimals', function () {
    $refueling = new Refueling(['quantity' => 40.123, 'unit_price' => 5.79]);

    // 40.123 * 5.79 = 232.31217 → rounds to 232.31
    expect($refueling->total_cost)->toBe(232.31);
});

it('computes total_cost as zero when quantity is zero', function () {
    $refueling = new Refueling(['quantity' => 0, 'unit_price' => 5.79]);

    expect($refueling->total_cost)->toBe(0.0);
});
