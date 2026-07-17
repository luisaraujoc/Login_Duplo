<?php

use App\Models\Account;
use App\Models\User;

it('lists only the accounts the logged-in user belongs to', function () {
    [$user, $myAccount] = actingAsAccountMember(['name' => 'Minha Conta']);

    $otherAccount = Account::factory()->create(['name' => 'Conta de Outra Pessoa']);
    $otherAccount->users()->attach(User::factory()->create());

    $this->getJson('/api/accounts')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Minha Conta');
});

it('creates a new account and attaches the creator as owner', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson('/api/accounts', [
        'name' => 'Conta Nova',
        'owner_name' => 'Fulano',
    ])->assertCreated()->assertJsonPath('data.name', 'Conta Nova');

    $account = Account::query()->where('name', 'Conta Nova')->firstOrFail();
    expect($account->users()->whereKey($user->id)->exists())->toBeTrue();
    expect($account->users()->wherePivot('role', 'owner')->whereKey($user->id)->exists())->toBeTrue();
});

it('selects an account the user belongs to', function () {
    [$user, $account] = actingAsAccountMember();

    $this->postJson("/api/accounts/{$account->id}/select")
        ->assertOk()
        ->assertJsonPath('data.id', $account->id);

    $this->getJson('/api/accounts/current')->assertOk()->assertJsonPath('data.id', $account->id);
});

it('refuses to select an account the user does not belong to', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create();
    $this->actingAs($user);

    $this->postJson("/api/accounts/{$account->id}/select")->assertForbidden();
});

it('reports no active account when none has been selected yet', function () {
    [$user] = actingAsAccountMember();

    $this->getJson('/api/accounts/current')->assertOk()->assertJsonPath('data', null);
});

it('does not require an active account to check /accounts/current itself', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->getJson('/api/accounts/current')->assertOk()->assertJsonPath('data', null);
});
