<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plate' => $this->plate,
            'brand' => $this->brand,
            'model' => $this->model,
            'manufacture_year' => $this->manufacture_year,
            'model_year' => $this->model_year,
            'renavam' => $this->renavam,
            'chassis' => $this->chassis,
        ];
    }
}
