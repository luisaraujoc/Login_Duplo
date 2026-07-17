<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Every test gets a fresh in-memory SQLite database (RefreshDatabase) — no
| test can leak state into another, and none of them touch the real
| database/database.sqlite used for local development.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Shared setup for the "logged in as a user who belongs to an account"
| scenario that almost every feature test needs — mirrors the app's own
| two-step login (see AuthController + AccountController).
|
*/

/**
 * Creates a user and an account, links them, and authenticates the test
 * as that user via Sanctum. Does NOT select the account in the session —
 * combine with withAccountSession() for routes behind account.selected.
 *
 * @return array{0: User, 1: Account}
 */
function actingAsAccountMember(array $accountAttributes = []): array
{
    $user = User::factory()->create();
    $account = Account::factory()->create($accountAttributes);
    $account->users()->attach($user, ['role' => 'owner']);

    test()->actingAs($user);

    return [$user, $account];
}

/**
 * The session payload that marks $account as the active one for this
 * request — pass to withSession(), mirroring what
 * POST /accounts/{account}/select does under the hood.
 */
function activeAccountSession(Account $account): array
{
    return ['active_account_id' => $account->id];
}
