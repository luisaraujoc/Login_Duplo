<?php

namespace App\Models;

use App\Enums\CashFlowType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'account_id', 'book_id', 'category_id', 'page_number',
    'type', 'description', 'amount', 'movement_date',
])]
class Movement extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Account, Movement>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Book, Movement>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo<Category, Movement>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'type' => CashFlowType::class,
            'amount' => 'decimal:2',
            'movement_date' => 'date',
            // Only present when hydrated from BalanceService's ledger subquery.
            'running_balance' => 'decimal:2',
        ];
    }
}
