<?php

use App\Models\Book;
use App\Models\Movement;

it('requires an active account before generating a report', function () {
    actingAsAccountMember();

    $this->getJson('/api/reports/monthly-pdf')->assertStatus(409);
    $this->getJson('/api/reports/period-pdf?date_from=2026-07-01&date_to=2026-07-31')->assertStatus(409);
});

it('generates a monthly PDF report with the correct content type', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);
    Movement::factory()->credit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-05', 'amount' => 1000,
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->get('/api/reports/monthly-pdf?month=7&year=2026');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('generates a period PDF report with the correct content type', function () {
    [$user, $account] = actingAsAccountMember();
    $book = Book::factory()->create(['account_id' => $account->id]);
    Movement::factory()->debit()->create([
        'account_id' => $account->id, 'book_id' => $book->id,
        'movement_date' => '2026-07-05', 'amount' => 250,
    ]);

    $response = $this->withSession(activeAccountSession($account))
        ->get('/api/reports/period-pdf?date_from=2026-07-01&date_to=2026-07-31');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('generates an empty-but-valid report for a period with no movements', function () {
    [$user, $account] = actingAsAccountMember();

    $response = $this->withSession(activeAccountSession($account))
        ->get('/api/reports/period-pdf?date_from=2026-01-01&date_to=2026-01-31');

    $response->assertOk();
    expect($response->getContent())->toStartWith('%PDF');
});

it('blocks report routes for a guest', function () {
    $this->getJson('/api/reports/monthly-pdf')->assertUnauthorized();
});
