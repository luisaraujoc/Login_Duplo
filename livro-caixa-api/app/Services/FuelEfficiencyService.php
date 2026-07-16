<?php

namespace App\Services;

use App\Models\Refueling;
use Illuminate\Support\Collection;

/**
 * Rebuilds the legacy mediakm/mediakm2 views (km rodado, km/l, R$/km,
 * l/km per vehicle) as plain PHP over an ordered collection, instead of
 * the original self-correlated subqueries keyed off id_abast ordering.
 */
class FuelEfficiencyService
{
    /**
     * @param  Collection<int, Refueling>  $refuelings
     * @return array<int, array<string, mixed>>
     */
    public function forVehicle(Collection $refuelings): array
    {
        $ordered = $refuelings->sortBy([
            ['refueled_at', 'asc'],
            ['odometer_km', 'asc'],
        ])->values();

        $previous = null;
        $rows = [];

        foreach ($ordered as $refueling) {
            $kmTraveled = $previous ? $refueling->odometer_km - $previous->odometer_km : null;
            $kmPerLiter = $kmTraveled && $refueling->quantity > 0
                ? round($kmTraveled / (float) $refueling->quantity, 3)
                : null;
            $costPerKm = $kmTraveled && $kmTraveled > 0
                ? round((float) $refueling->total_cost / $kmTraveled, 4)
                : null;
            $litersPerKm = $kmTraveled && $kmTraveled > 0
                ? round((float) $refueling->quantity / $kmTraveled, 4)
                : null;

            $rows[] = [
                'refueling_id' => $refueling->id,
                'previous_odometer_km' => $previous?->odometer_km,
                'km_traveled' => $kmTraveled,
                'km_per_liter' => $kmPerLiter,
                'cost_per_km' => $costPerKm,
                'liters_per_km' => $litersPerKm,
                'meets_target' => $kmPerLiter !== null
                    ? $kmPerLiter >= (float) $refueling->target_efficiency_km_per_liter
                    : null,
            ];

            $previous = $refueling;
        }

        return $rows;
    }
}
