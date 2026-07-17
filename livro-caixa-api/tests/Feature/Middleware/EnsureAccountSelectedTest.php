<?php

use App\Models\Account;

it('blocks with 409 when no account was ever selected', function () {
    actingAsAccountMember();

    $this->getJson('/api/books')->assertStatus(409)->assertJsonPath('message', 'Nenhuma conta selecionada.');
});

it('blocks with 409 when the session points to an account the user no longer has access to', function () {
    [$user, $account] = actingAsAccountMember();

    // Simulate a stale session: the id is set, but the user's membership
    // was revoked (or never existed) by the time the request comes in.
    $account->users()->detach($user);

    $this->withSession(activeAccountSession($account))
        ->getJson('/api/books')
        ->assertStatus(409);
});

it('blocks with 409 when the session points to an account that no longer exists', function () {
    actingAsAccountMember();

    $this->withSession(['active_account_id' => 999999])
        ->getJson('/api/books')
        ->assertStatus(409);
});

it('passes through once a valid account is selected', function () {
    [$user, $account] = actingAsAccountMember();

    $this->withSession(activeAccountSession($account))
        ->getJson('/api/books')
        ->assertOk();
});

it('does not affect global routes that sit outside the account.selected group', function () {
    actingAsAccountMember();

    // No account selected at all, yet these must still work.
    $this->getJson('/api/categories')->assertOk();
    $this->getJson('/api/vehicles')->assertOk();
});
