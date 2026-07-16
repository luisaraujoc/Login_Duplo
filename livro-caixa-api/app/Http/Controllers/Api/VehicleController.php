<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefuelingResource;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\FuelEfficiencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Fleet vehicles — global, not scoped to a ledger account (the legacy
 * veiculos table has no idconta column).
 */
class VehicleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return VehicleResource::collection(Vehicle::query()->orderBy('plate')->get());
    }

    public function store(Request $request): VehicleResource
    {
        $data = $request->validate([
            'plate' => ['required', 'string', 'max:10'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'manufacture_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'model_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'renavam' => ['nullable', 'string', 'max:20'],
            'chassis' => ['nullable', 'string', 'max:50'],
        ]);

        return new VehicleResource(Vehicle::query()->create($data));
    }

    public function show(Vehicle $vehicle): VehicleResource
    {
        return new VehicleResource($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): VehicleResource
    {
        $data = $request->validate([
            'plate' => ['required', 'string', 'max:10'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'manufacture_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'model_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'renavam' => ['nullable', 'string', 'max:20'],
            'chassis' => ['nullable', 'string', 'max:50'],
        ]);

        $vehicle->update($data);

        return new VehicleResource($vehicle);
    }

    public function destroy(Vehicle $vehicle): Response
    {
        $vehicle->delete();

        return response()->noContent();
    }

    /**
     * Fuel-efficiency history for a vehicle: each refueling paired with the
     * km/l, R$/km and l/km computed against the previous refueling — the
     * legacy mediakm/mediakm2 views, rebuilt without correlated subqueries.
     */
    public function efficiency(Vehicle $vehicle, FuelEfficiencyService $service): AnonymousResourceCollection
    {
        $refuelings = $vehicle->refuelings()
            ->with(['fuelSupplier', 'fuelProduct', 'nfeLink'])
            ->get();

        return RefuelingResource::collection($refuelings)
            ->additional(['meta' => ['efficiency' => $service->forVehicle($refuelings)]]);
    }
}
