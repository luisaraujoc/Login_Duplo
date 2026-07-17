<?php

use App\Models\Account;
use App\Models\Book;
use App\Models\Movement;
use App\Services\BalanceService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->service = new BalanceService();
    $this->account = Account::factory()->create();
    $this->book = Book::factory()->create(['account_id' => $this->account->id]);
});

it('ledgerByDate assigns a chronologically accumulating running balance', function () {
    Movement::factory()->debit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-10', 'amount' => 300,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-01', 'amount' => 1000,
    ]);
    Movement::factory()->debit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-05', 'amount' => 200,
    ]);

    $ledger = $this->service->ledgerByDate($this->account->id)->get();

    expect($ledger->pluck('movement_date')->map->toDateString()->all())
        ->toBe(['2026-07-01', '2026-07-05', '2026-07-10']);
    expect($ledger->pluck('running_balance')->map(fn ($v) => (float) $v)->all())
        ->toBe([1000.0, 800.0, 500.0]);
});

it('ledgerByDate scopes strictly to the given account', function () {
    $otherAccount = Account::factory()->create();
    $otherBook = Book::factory()->create(['account_id' => $otherAccount->id]);

    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id, 'amount' => 100,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $otherAccount->id, 'book_id' => $otherBook->id, 'amount' => 999,
    ]);

    $ledger = $this->service->ledgerByDate($this->account->id)->get();

    expect($ledger)->toHaveCount(1);
    expect((float) $ledger->first()->running_balance)->toEqual(100.0);
});

it('ledgerByBookPage orders by book and page number instead of date', function () {
    $bookTwo = Book::factory()->create(['account_id' => $this->account->id, 'number' => 2]);

    // Recorded in book 2 first chronologically, but book 1/page 1 should
    // still come first in this ledger because it orders by book, then page.
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $bookTwo->id,
        'page_number' => 1, 'movement_date' => '2026-01-01', 'amount' => 50,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'page_number' => 1, 'movement_date' => '2026-07-01', 'amount' => 100,
    ]);

    $ledger = $this->service->ledgerByBookPage($this->account->id)->get();

    expect($ledger->pluck('book_id')->all())->toBe([$this->book->id, $bookTwo->id]);
    expect((float) $ledger->first()->running_balance)->toEqual(100.0);
    expect((float) $ledger->last()->running_balance)->toEqual(150.0);
});

it('balanceBefore returns 0 when there is no history yet', function () {
    $balance = $this->service->balanceBefore($this->account->id, Carbon::parse('2026-07-01'));

    expect($balance)->toBe(0.0);
});

it('balanceBefore excludes movements on the boundary date itself', function () {
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-01', 'amount' => 500,
    ]);

    expect($this->service->balanceBefore($this->account->id, Carbon::parse('2026-07-01')))->toBe(0.0);
    expect($this->service->balanceBefore($this->account->id, Carbon::parse('2026-07-02')))->toBe(500.0);
});

it('summaryForRange computes opening balance, credits, debits and closing balance', function () {
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-06-15', 'amount' => 1000,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-05', 'amount' => 500,
    ]);
    Movement::factory()->debit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-06', 'amount' => 120,
    ]);

    $summary = $this->service->summaryForRange(
        $this->account->id,
        Carbon::parse('2026-07-01'),
        Carbon::parse('2026-07-31'),
    );

    expect($summary)->toBe([
        'opening_balance' => 1000.0,
        'credits' => 500.0,
        'debits' => 120.0,
        'balance' => 380.0,
        'closing_balance' => 1380.0,
    ]);
});

it('summaryForRange treats a period with no movements as all zeros plus the opening balance', function () {
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-01-01', 'amount' => 250,
    ]);

    $summary = $this->service->summaryForRange(
        $this->account->id,
        Carbon::parse('2026-07-01'),
        Carbon::parse('2026-07-31'),
    );

    expect($summary)->toBe([
        'opening_balance' => 250.0,
        'credits' => 0.0,
        'debits' => 0.0,
        'balance' => 0.0,
        'closing_balance' => 250.0,
    ]);
});

it('summaryForMonth returns both the month block and the year-to-date block', function () {
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-01-10', 'amount' => 1000,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-01', 'amount' => 1500,
    ]);
    Movement::factory()->debit()->create([
        'account_id' => $this->account->id, 'book_id' => $this->book->id,
        'movement_date' => '2026-07-04', 'amount' => 320.50,
    ]);

    $summary = $this->service->summaryForMonth($this->account->id, 7, 2026);

    expect($summary['month'])->toBe([
        'opening_balance' => 1000.0,
        'credits' => 1500.0,
        'debits' => 320.5,
        'balance' => 1179.5,
        'closing_balance' => 2179.5,
    ]);
    expect($summary['year'])->toBe([
        'opening_balance' => 0.0,
        'credits' => 2500.0,
        'debits' => 320.5,
        'balance' => 2179.5,
        'closing_balance' => 2179.5,
    ]);
});
