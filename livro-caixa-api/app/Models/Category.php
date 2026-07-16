<?php

namespace App\Models;

use App\Enums\CashFlowType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type'])]
class Category extends Model
{
    use HasFactory;

    /**
     * @return HasMany<Movement, Category>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    protected function casts(): array
    {
        return [
            'type' => CashFlowType::class,
        ];
    }
}
