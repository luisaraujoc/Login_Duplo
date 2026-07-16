<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FuelSupplierResource;
use App\Models\FuelSupplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FuelSupplierController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FuelSupplierResource::collection(FuelSupplier::query()->orderBy('company_name')->get());
    }

    public function store(Request $request): FuelSupplierResource
    {
        $data = $request->validate($this->rules());

        return new FuelSupplierResource(FuelSupplier::query()->create($data));
    }

    public function show(FuelSupplier $fuelSupplier): FuelSupplierResource
    {
        return new FuelSupplierResource($fuelSupplier);
    }

    public function update(Request $request, FuelSupplier $fuelSupplier): FuelSupplierResource
    {
        $data = $request->validate($this->rules());

        $fuelSupplier->update($data);

        return new FuelSupplierResource($fuelSupplier);
    }

    public function destroy(FuelSupplier $fuelSupplier): Response
    {
        $fuelSupplier->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:250'],
            'address' => ['required', 'string', 'max:150'],
            'neighborhood' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'max:100'],
            'cnpj' => ['required', 'string', 'max:14'],
        ];
    }
}
