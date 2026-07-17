<?php

use App\Models\Refueling;
use App\Services\FuelEfficiencyService;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->service = new FuelEfficiencyService();
});

/**
 * Builds an in-memory (unsaved) Refueling — this service only ever reads
 * from the collection it's handed, so hitting the database is unnecessary.
 */
function refueling(int $id, string $refueledAt, int $odometerKm, float $quantity, float $unitPrice, float $target = 14.10): Refueling
{
    $refueling = new Refueling([
        'refueled_at' => $refueledAt,
        'odometer_km' => $odometerKm,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'target_efficiency_km_per_liter' => $target,
    ]);
    $refueling->id = $id;

    return $refueling;
}

it('leaves the first refueling without any comparison figures', function () {
    $rows = $this->service->forVehicle(new Collection([
        refueling(1, '2026-07-01 08:00', 10000, 40, 5.79),
    ]));

    expect($rows)->toHaveCount(1);
    expect($rows[0])->toBe([
        'refueling_id' => 1,
        'previous_odometer_km' => null,
        'km_traveled' => null,
        'km_per_liter' => null,
        'cost_per_km' => null,
        'liters_per_km' => null,
        'meets_target' => null,
    ]);
});

it('computes km/l, cost/km, l/km and meets_target against the previous refueling', function () {
    $rows = $this->service->forVehicle(new Collection([
        refueling(1, '2026-07-01 08:00', 10000, 40, 5.79, target: 14.10),
        refueling(2, '2026-07-10 08:00', 10560, 42, 5.85, target: 14.10),
    ]));

    $second = collect($rows)->firstWhere('refueling_id', 2);

    expect($second['previous_odometer_km'])->toBe(10000);
    expect($second['km_traveled'])->toBe(560);
    expect($second['km_per_liter'])->toBe(round(560 / 42, 3));
    expect($second['cost_per_km'])->toBe(round((42 * 5.85) / 560, 4));
    expect($second['liters_per_km'])->toBe(round(42 / 560, 4));
    expect($second['meets_target'])->toBeFalse(); // 13.33 km/l < 14.10 target
});

it('sorts by refueled_at before computing, regardless of collection order', function () {
    $rows = $this->service->forVehicle(new Collection([
        refueling(2, '2026-07-10 08:00', 10560, 42, 5.85), // later, but listed first
        refueling(1, '2026-07-01 08:00', 10000, 40, 5.79),
    ]));

    $second = collect($rows)->firstWhere('refueling_id', 2);
    expect($second['previous_odometer_km'])->toBe(10000);
    expect($second['km_traveled'])->toBe(560);
});

it('does not divide by zero when quantity or km_traveled is zero', function () {
    $rows = $this->service->forVehicle(new Collection([
        refueling(1, '2026-07-01 08:00', 10000, 40, 5.79),
        refueling(2, '2026-07-02 08:00', 10000, 10, 5.79), // same odometer as before
    ]));

    $second = collect($rows)->firstWhere('refueling_id', 2);

    // km_traveled is a real, reportable 0 — but every ratio that would
    // divide by it (km/l, cost/km, l/km) is null, not 0 or NAN/INF: 0 is
    // falsy in PHP, so the service's `$kmTraveled && ...` guards skip the
    // division entirely rather than computing a misleading "0 km/l".
    expect($second['km_traveled'])->toBe(0);
    expect($second['km_per_liter'])->toBeNull();
    expect($second['cost_per_km'])->toBeNull();
    expect($second['liters_per_km'])->toBeNull();
});

it('handles three consecutive refuelings, chaining the running odometer correctly', function () {
    $rows = $this->service->forVehicle(new Collection([
        refueling(1, '2026-07-01 08:00', 10000, 40, 5.00),
        refueling(2, '2026-07-10 08:00', 10500, 40, 5.00),
        refueling(3, '2026-07-20 08:00', 11100, 50, 5.00),
    ]));

    $third = collect($rows)->firstWhere('refueling_id', 3);

    expect($third['previous_odometer_km'])->toBe(10500);
    expect($third['km_traveled'])->toBe(600);
    expect($third['km_per_liter'])->toBe(round(600 / 50, 3));
});
