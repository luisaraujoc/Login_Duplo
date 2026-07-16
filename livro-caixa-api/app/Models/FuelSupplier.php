<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_name', 'address', 'neighborhood', 'city', 'state', 'zip_code', 'phone', 'cnpj'])]
class FuelSupplier extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Refueling, FuelSupplier>
     */
    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class);
    }
}
