<?php

use App\Models\Account;
use App\Models\Book;
use App\Models\Category;
use App\Models\Movement;

it('requires an active account before listing movements', function () {
    actingAsAccountMember();

    $this->getJson('/api/movements')->assertStatus(409);
});

it('creates a movement under the active account and book', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id, 'number' => 1]);
    $category = Category::factory()->credit()->create();

    $this->withSession(activeAccountSession($account))
        ->postJson('/api/movements', [
            'book_id' => $book->id,
            'category_id' => $category->id,
            'page_number' => 1,
            'type' => 'credit',
            'description' => 'Salário de julho',
            'amount' => 1500,
            'movement_date' => '2026-07-05',
        ])
        ->assertCreated()
        ->assertJsonPath('data.description', 'Salário de julho')
        ->assertJsonPath('data.amount', 1500)
        ->assertJsonPath('data.type', 'credit');

    expect(Movement::query()->where('account_id', $account->id)->count())->toBe(1);
});

it('refuses to create a movement in a book from another account', function () {
    [$user, $account] = actingAsAccountMember();
    $otherAccount = Account::factory()->create();
    $foreignBook = Book::factory()->create(['account_id' => $otherAccount->id]);

    $this->withSession(activeAccountSession($account))
        ->postJson('/api/movements', [
            'book_id' => $foreignBook->id,
            'page_number' => 1,
            'type' => 'credit',
            'description' => 'Tentativa inválida',
            'amount' => 100,
            'movement_date' => '2026-07-05',
        ])
        ->assertUnprocessable();

    expect(Movement::query()->count())->toBe(0);
});

it('returns 404 for a movement belonging to another account', function () {
    [$user, $account] = actingAsAccountMember();
    $foreignMovement = Movement::factory()->create();

    $this->withSession(activeAccountSession($account))
        ->getJson("/api/movements/{$foreignMovement->id}")
        ->assertNotFound();
});

it('updates and deletes a movement', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);
    $movement = Movement::factory()->create([
        'account_id' => $account->id,
        'book_id' => $book->id,
        'description' => 'Original',
    ]);

    $this->withSession(activeAccountSession($account))
        ->putJson("/api/movements/{$movement->id}", [
            'book_id' => $book->id,
            'category_id' => null,
            'page_number' => $movement->page_number,
            'type' => $movement->type->value,
            'description' => 'Atualizado',
            'amount' => 250.75,
            'movement_date' => $movement->movement_date->toDateString(),
        ])
        ->assertOk()
        ->assertJsonPath('data.description', 'Atualizado')
        ->assertJsonPath('data.amount', 250.75);

    $this->withSession(activeAccountSession($account))
        ->deleteJson("/api/movements/{$movement->id}")
        ->assertNoContent();

    expect(Movement::query()->find($movement->id))->toBeNull();
});

it('computes the running balance line by line, in chronological order', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    // Deliberately created out of chronological order to prove the ledger
    // sorts by movement_date, not by insertion/id order.
    Movement::factory()->debit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-10', 'amount' => 300,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-01', 'amount' => 1000,
    ]);
    Movement::factory()->debit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-05', 'amount' => 200,
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->getJson('/api/movements?month=7&year=2026')
        ->assertOk();

    $balances = collect($response->json('data'))->pluck('running_balance', 'movement_date');

    expect((float) $balances['2026-07-01'])->toEqual(1000.0);
    expect((float) $balances['2026-07-05'])->toEqual(800.0);
    expect((float) $balances['2026-07-10'])->toEqual(500.0);
});

it('carries the opening balance from before the filtered window', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-06-15', 'amount' => 500,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-01', 'amount' => 200,
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->getJson('/api/movements?month=7&year=2026')
        ->assertOk();

    expect((float) $response->json('data.0.running_balance'))->toEqual(700.0);
});

it('filters movements by date range and description search', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    Movement::factory()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-05', 'description' => 'Conta de luz',
    ]);
    Movement::factory()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-06', 'description' => 'Supermercado',
    ]);
    Movement::factory()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-08-01', 'description' => 'Conta de água (fora do período)',
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->getJson('/api/movements?date_from=2026-07-01&date_to=2026-07-31&q=conta')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.description'))->toBe('Conta de luz');
});

it('filters movements by book and page range', function () {
    [$user, $account] = actingAsAccountMember();
    $bookOne = Book::factory()->create(['account_id' => $account->id, 'number' => 1]);
    $bookTwo = Book::factory()->create(['account_id' => $account->id, 'number' => 2]);

    Movement::factory()->create(['account_id' => $account->id, 'book_id' => $bookOne->id, 'page_number' => 1]);
    Movement::factory()->create(['account_id' => $account->id, 'book_id' => $bookOne->id, 'page_number' => 5]);
    Movement::factory()->create(['account_id' => $account->id, 'book_id' => $bookOne->id, 'page_number' => 9]);
    Movement::factory()->create(['account_id' => $account->id, 'book_id' => $bookTwo->id, 'page_number' => 1]);

    $response = $this->withSession(activeAccountSession($account))
        ->getJson("/api/movements?book_id={$bookOne->id}&page_from=2&page_to=6")
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.page_number'))->toBe(5);
});

it('summarizes month and year-to-date totals', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-01-10', 'amount' => 1000,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-01', 'amount' => 1500,
    ]);
    Movement::factory()->debit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-04', 'amount' => 320.50,
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->getJson('/api/movements/summary?month=7&year=2026')
        ->assertOk();

    // Month (July): opening balance carries the January credit in (it
    // happened before July 1st), so it's 1000 — not 0.
    $response->assertJsonPath('data.month.opening_balance', 1000)
        ->assertJsonPath('data.month.credits', 1500)
        ->assertJsonPath('data.month.debits', 320.5)
        ->assertJsonPath('data.month.closing_balance', 2179.5)
        // Year to date (Jan 1 – Jul 31): nothing came before Jan 1st.
        ->assertJsonPath('data.year.opening_balance', 0)
        ->assertJsonPath('data.year.credits', 2500)
        ->assertJsonPath('data.year.closing_balance', 2179.5);
});

it('summarizes an arbitrary date range as a single flat block', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-06-01', 'amount' => 100,
    ]);
    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-10', 'amount' => 400,
    ]);

    $this->withSession(activeAccountSession($account))
        ->getJson('/api/movements/summary?date_from=2026-07-01&date_to=2026-07-31')
        ->assertOk()
        ->assertJsonPath('data.opening_balance', 100)
        ->assertJsonPath('data.credits', 400)
        ->assertJsonPath('data.closing_balance', 500)
        ->assertJsonMissingPath('data.month');
});

it('blocks movement routes for a guest', function () {
    $this->getJson('/api/movements')->assertUnauthorized();
});
