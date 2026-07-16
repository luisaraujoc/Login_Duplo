<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['url'])]
class NfeLink extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Refueling, NfeLink>
     */
    public function refuelings(): HasMany
    {
        return $this->hasMany(Refueling::class);
    }
}
