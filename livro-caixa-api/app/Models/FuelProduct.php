<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'unit'])]
class FuelProduct extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Refueling, FuelProduct>
     */
    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class);
    }
}
