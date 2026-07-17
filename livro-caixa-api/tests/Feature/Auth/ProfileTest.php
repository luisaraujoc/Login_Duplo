<?php

use App\Models\User;

it('updates the logged-in user name and e-mail', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    $this->actingAs($user);

    $this->putJson('/api/me', [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ])->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', 'new@example.com');

    expect($user->refresh()->name)->toBe('New Name');
    expect($user->refresh()->email)->toBe('new@example.com');
});

it('rejects updating to an e-mail already used by someone else', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'mine@example.com']);
    $this->actingAs($user);

    $this->putJson('/api/me', [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('allows keeping your own current e-mail unchanged', function () {
    $user = User::factory()->create(['email' => 'mine@example.com']);
    $this->actingAs($user);

    $this->putJson('/api/me', [
        'name' => 'Same Person, New Name',
        'email' => 'mine@example.com',
    ])->assertOk();
});

it('blocks profile update for a guest', function () {
    $this->putJson('/api/me', ['name' => 'X', 'email' => 'x@example.com'])->assertUnauthorized();
});
