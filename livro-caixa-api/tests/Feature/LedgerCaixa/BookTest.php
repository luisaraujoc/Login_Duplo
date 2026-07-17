<?php

use App\Models\Account;
use App\Models\Book;
use App\Models\Movement;

it('requires an active account before listing books', function () {
    [$user, $account] = actingAsAccountMember();

    $this->getJson('/api/books')->assertStatus(409);
});

it('lists only books belonging to the active account', function () {
    [$user, $account] = actingAsAccountMember();
    Book::factory()->count(2)->create(['account_id' => $account->id]);

    $otherAccount = Account::factory()->create();
    Book::factory()->create(['account_id' => $otherAccount->id]);

    $this->withSession(activeAccountSession($account))
        ->getJson('/api/books')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a book under the active account', function () {
    [$user, $account] = actingAsAccountMember();

    $this->withSession(activeAccountSession($account))
        ->postJson('/api/books', ['number' => 1, 'label' => 'Livro 2026'])
        ->assertCreated()
        ->assertJsonPath('data.number', 1)
        ->assertJsonPath('data.label', 'Livro 2026');

    expect(Book::query()->where('account_id', $account->id)->where('number', 1)->exists())->toBeTrue();
});

it('returns 404 for a book belonging to a different account', function () {
    [$user, $account] = actingAsAccountMember();
    $otherAccount = Account::factory()->create();
    $foreignBook = Book::factory()->create(['account_id' => $otherAccount->id]);

    $this->withSession(activeAccountSession($account))
        ->getJson("/api/books/{$foreignBook->id}")
        ->assertNotFound();
});

it('updates a book belonging to the active account', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id, 'number' => 1]);

    $this->withSession(activeAccountSession($account))
        ->putJson("/api/books/{$book->id}", ['number' => 2, 'label' => 'Renomeado'])
        ->assertOk()
        ->assertJsonPath('data.number', 2);

    expect($book->refresh()->label)->toBe('Renomeado');
});

it('deletes a book with no movements', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);

    $this->withSession(activeAccountSession($account))
        ->deleteJson("/api/books/{$book->id}")
        ->assertNoContent();

    expect(Book::query()->find($book->id))->toBeNull();
});

it('refuses to delete a book that still has movements', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);
    Movement::factory()->create(['account_id' => $account->id, 'book_id' => $book->id]);

    $this->withSession(activeAccountSession($account))
        ->deleteJson("/api/books/{$book->id}")
        ->assertUnprocessable();

    expect(Book::query()->find($book->id))->not->toBeNull();
});
