<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vehicle_id', 'fuel_supplier_id', 'fuel_product_id', 'nfe_link_id',
    'invoice_number', 'access_key', 'refueled_at', 'odometer_km',
    'quantity', 'unit_price', 'notes', 'target_efficiency_km_per_liter',
])]
class Refueling extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Vehicle, Refueling>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return BelongsTo<FuelSupplier, Refueling>
     */
    public function fuelSupplier(): BelongsTo
    {
        return $this->belongsTo(FuelSupplier::class);
    }

    /**
     * @return BelongsTo<FuelProduct, Refueling>
     */
    public function fuelProduct(): BelongsTo
    {
        return $this->belongsTo(FuelProduct::class);
    }

    /**
     * @return BelongsTo<NfeLink, Refueling>
     */
    public function nfeLink(): BelongsTo
    {
        return $this->belongsTo(NfeLink::class);
    }

    /**
     * Mirrors the legacy valorcompra generated column (quantity * valor_unit).
     */
    protected function totalCost(): Attribute
    {
        return Attribute::get(fn () => round($this->quantity * $this->unit_price, 2));
    }

    protected function casts(): array
    {
        return [
            'refueled_at' => 'datetime',
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:4',
            'target_efficiency_km_per_liter' => 'decimal:2',
        ];
    }
}
