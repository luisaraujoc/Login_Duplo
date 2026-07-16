<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['plate', 'brand', 'model', 'manufacture_year', 'model_year', 'renavam', 'chassis'])]
class Vehicle extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Refueling, Vehicle>
     */
    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class)->orderBy('odometer_km');
    }
}
