<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RefuelingResource;
use App\Models\Refueling;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RefuelingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Refueling::query()
            ->with(['vehicle', 'fuelSupplier', 'fuelProduct', 'nfeLink'])
            ->latest('refueled_at');

        if ($vehicleId = $request->integer('vehicle_id')) {
            $query->where('vehicle_id', $vehicleId);
        }

        return RefuelingResource::collection($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): RefuelingResource
    {
        $data = $request->validate($this->rules());
        $data['target_efficiency_km_per_liter'] ??= 14.10;

        $refueling = Refueling::query()->create($data);
        $refueling->load(['vehicle', 'fuelSupplier', 'fuelProduct', 'nfeLink']);

        return new RefuelingResource($refueling);
    }

    public function show(Refueling $refueling): RefuelingResource
    {
        $refueling->load(['vehicle', 'fuelSupplier', 'fuelProduct', 'nfeLink']);

        return new RefuelingResource($refueling);
    }

    public function update(Request $request, Refueling $refueling): RefuelingResource
    {
        $data = $request->validate($this->rules());

        $refueling->update($data);
        $refueling->load(['vehicle', 'fuelSupplier', 'fuelProduct', 'nfeLink']);

        return new RefuelingResource($refueling);
    }

    public function destroy(Refueling $refueling): Response
    {
        $refueling->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'fuel_supplier_id' => ['required', 'exists:fuel_suppliers,id'],
            'fuel_product_id' => ['required', 'exists:fuel_products,id'],
            'nfe_link_id' => ['nullable', 'exists:nfe_links,id'],
            'invoice_number' => ['required', 'string', 'max:20'],
            'access_key' => ['nullable', 'string', 'max:60'],
            'refueled_at' => ['required', 'date'],
            'odometer_km' => ['required', 'integer', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_price' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:255'],
            'target_efficiency_km_per_liter' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
