<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'number', 'label'])]
class Book extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Account, Book>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return HasMany<Movement, Book>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
