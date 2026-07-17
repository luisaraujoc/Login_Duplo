<?php

use App\Models\Refueling;
use App\Models\User;
use App\Models\Vehicle;

it('has no efficiency figures for a vehicles first ever refueling', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    $refueling = Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'odometer_km' => 10000,
        'refueled_at' => '2026-07-01 08:00:00',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->id}/efficiency")->assertOk();

    $entry = collect($response->json('meta.efficiency'))->firstWhere('refueling_id', $refueling->id);

    expect($entry['previous_odometer_km'])->toBeNull();
    expect($entry['km_traveled'])->toBeNull();
    expect($entry['km_per_liter'])->toBeNull();
    expect($entry['meets_target'])->toBeNull();
});

it('computes km/l, cost/km and whether the target was met between two refuelings', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'odometer_km' => 10000,
        'refueled_at' => '2026-07-01 08:00:00',
        'quantity' => 40,
        'unit_price' => 5.79,
        'target_efficiency_km_per_liter' => 14.10,
    ]);

    $second = Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'odometer_km' => 10560, // 560 km since the first refueling
        'refueled_at' => '2026-07-10 08:00:00',
        'quantity' => 42,
        'unit_price' => 5.85,
        'target_efficiency_km_per_liter' => 14.10,
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->id}/efficiency")->assertOk();

    $entry = collect($response->json('meta.efficiency'))->firstWhere('refueling_id', $second->id);

    expect($entry['previous_odometer_km'])->toBe(10000);
    expect($entry['km_traveled'])->toBe(560);
    // 560 km / 42 L = 13.333 km/l — below the 14.10 target.
    expect($entry['km_per_liter'])->toBe(13.333);
    expect($entry['meets_target'])->toBeFalse();
    // total_cost = 42 * 5.85 = 245.70; cost_per_km = 245.70 / 560 = 0.4388
    expect($entry['cost_per_km'])->toBe(0.4388);
});

it('flags meets_target true when the vehicle beats its efficiency goal', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'odometer_km' => 0,
        'refueled_at' => '2026-07-01 08:00:00',
    ]);

    $second = Refueling::factory()->create([
        'vehicle_id' => $vehicle->id,
        'odometer_km' => 1000, // 1000 km
        'refueled_at' => '2026-07-02 08:00:00',
        'quantity' => 50, // 20 km/l — comfortably above any sane target
        'target_efficiency_km_per_liter' => 14.10,
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->id}/efficiency")->assertOk();

    $entry = collect($response->json('meta.efficiency'))->firstWhere('refueling_id', $second->id);

    expect((float) $entry['km_per_liter'])->toEqual(20.0);
    expect($entry['meets_target'])->toBeTrue();
});

it('orders efficiency entries chronologically regardless of creation order', function () {
    $this->actingAs(User::factory()->create());
    $vehicle = Vehicle::factory()->create();

    // Created out of order on purpose.
    $later = Refueling::factory()->create([
        'vehicle_id' => $vehicle->id, 'odometer_km' => 2000, 'refueled_at' => '2026-07-20 08:00:00',
    ]);
    $earlier = Refueling::factory()->create([
        'vehicle_id' => $vehicle->id, 'odometer_km' => 1000, 'refueled_at' => '2026-07-01 08:00:00',
    ]);

    $response = $this->getJson("/api/vehicles/{$vehicle->id}/efficiency")->assertOk();

    $laterEntry = collect($response->json('meta.efficiency'))->firstWhere('refueling_id', $later->id);

    expect($laterEntry['previous_odometer_km'])->toBe(1000);
    expect($laterEntry['km_traveled'])->toBe(1000);
});
