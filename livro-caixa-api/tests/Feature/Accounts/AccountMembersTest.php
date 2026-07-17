<?php

use App\Models\User;

it('lists everyone with access to the account', function () {
    [$owner, $account] = actingAsAccountMember();
    $second = User::factory()->create();
    $account->users()->attach($second, ['role' => 'member']);

    $this->getJson("/api/accounts/{$account->id}/users")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('invites an existing user to the account', function () {
    [$owner, $account] = actingAsAccountMember();
    $invitee = User::factory()->create();

    $this->postJson("/api/accounts/{$account->id}/users", ['user_id' => $invitee->id])
        ->assertOk()
        ->assertJsonCount(2, 'data');

    expect($account->users()->whereKey($invitee->id)->exists())->toBeTrue();
    expect(
        $account->users()->wherePivot('role', 'member')->whereKey($invitee->id)->exists()
    )->toBeTrue();
});

it('refuses to invite someone who already has access', function () {
    [$owner, $account] = actingAsAccountMember();
    $invitee = User::factory()->create();
    $account->users()->attach($invitee);

    $this->postJson("/api/accounts/{$account->id}/users", ['user_id' => $invitee->id])
        ->assertUnprocessable();
});

it('refuses to invite a user id that does not exist', function () {
    [$owner, $account] = actingAsAccountMember();

    $this->postJson("/api/accounts/{$account->id}/users", ['user_id' => 999999])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

it('removes a member from the account', function () {
    [$owner, $account] = actingAsAccountMember();
    $second = User::factory()->create();
    $account->users()->attach($second, ['role' => 'member']);

    $this->deleteJson("/api/accounts/{$account->id}/users/{$second->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data');

    expect($account->users()->whereKey($second->id)->exists())->toBeFalse();
});

it('refuses to remove the last remaining member of an account', function () {
    [$owner, $account] = actingAsAccountMember();

    $this->deleteJson("/api/accounts/{$account->id}/users/{$owner->id}")
        ->assertUnprocessable();

    expect($account->users()->whereKey($owner->id)->exists())->toBeTrue();
});

it('blocks a non-member from viewing, inviting or removing members', function () {
    $account = \App\Models\Account::factory()->create();
    $account->users()->attach(User::factory()->create());

    $outsider = User::factory()->create();
    $this->actingAs($outsider);

    $this->getJson("/api/accounts/{$account->id}/users")->assertForbidden();
    $this->postJson("/api/accounts/{$account->id}/users", ['user_id' => $outsider->id])->assertForbidden();
    $this->deleteJson("/api/accounts/{$account->id}/users/{$outsider->id}")->assertForbidden();
});
