<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefuelingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'vehicle_plate' => $this->whenLoaded('vehicle', fn () => $this->vehicle->plate),
            'fuel_supplier_id' => $this->fuel_supplier_id,
            'fuel_supplier_name' => $this->whenLoaded('fuelSupplier', fn () => $this->fuelSupplier->company_name),
            'fuel_product_id' => $this->fuel_product_id,
            'fuel_product_name' => $this->whenLoaded('fuelProduct', fn () => $this->fuelProduct->name),
            'nfe_link_id' => $this->nfe_link_id,
            'nfe_link_url' => $this->whenLoaded('nfeLink', fn () => $this->nfeLink?->url),
            'invoice_number' => $this->invoice_number,
            'access_key' => $this->access_key,
            'refueled_at' => $this->refueled_at->toIso8601String(),
            'odometer_km' => $this->odometer_km,
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_cost' => (float) $this->total_cost,
            'notes' => $this->notes,
            'target_efficiency_km_per_liter' => (float) $this->target_efficiency_km_per_liter,
        ];
    }
}
