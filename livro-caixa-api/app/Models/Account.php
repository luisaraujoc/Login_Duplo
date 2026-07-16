<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'owner_name'])]
class Account extends Model
{
    use HasFactory;

    /**
     * @return BelongsToMany<User, Account>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Book, Account>
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * @return HasMany<Movement, Account>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
