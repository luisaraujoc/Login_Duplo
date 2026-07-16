<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FuelProductResource;
use App\Models\FuelProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FuelProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FuelProductResource::collection(FuelProduct::query()->orderBy('name')->get());
    }

    public function store(Request $request): FuelProductResource
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:5'],
        ]);

        return new FuelProductResource(FuelProduct::query()->create($data));
    }

    public function show(FuelProduct $fuelProduct): FuelProductResource
    {
        return new FuelProductResource($fuelProduct);
    }

    public function update(Request $request, FuelProduct $fuelProduct): FuelProductResource
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:5'],
        ]);

        $fuelProduct->update($data);

        return new FuelProductResource($fuelProduct);
    }

    public function destroy(FuelProduct $fuelProduct): Response
    {
        $fuelProduct->delete();

        return response()->noContent();
    }
}
