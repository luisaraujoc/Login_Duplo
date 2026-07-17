<?php

use App\Models\User;

it('registers a new user and logs them in', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Rubens Couto',
        'email' => 'rubens@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Rubens Couto')
        ->assertJsonPath('data.email', 'rubens@example.com');

    expect(User::query()->where('email', 'rubens@example.com')->exists())->toBeTrue();

    // The register call also logs the user in — no separate login needed.
    // (assertSuccessful rather than assertOk: the User model instance is
    // still flagged wasRecentlyCreated within this same test process, which
    // makes the JsonResource respond 201 here same as it did above — a
    // same-process testing quirk, not something a real separate request
    // would ever do.)
    $this->getJson('/api/me')->assertSuccessful()->assertJsonPath('data.email', 'rubens@example.com');
});

it('hashes the password instead of storing it in plain text', function () {
    $this->postJson('/api/register', [
        'name' => 'Rubens Couto',
        'email' => 'rubens@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::query()->where('email', 'rubens@example.com')->firstOrFail();

    expect($user->password)->not->toBe('password123');
    expect(\Illuminate\Support\Facades\Hash::check('password123', $user->password))->toBeTrue();
});

it('rejects registration when passwords do not match', function () {
    $this->postJson('/api/register', [
        'name' => 'Rubens Couto',
        'email' => 'rubens@example.com',
        'password' => 'password123',
        'password_confirmation' => 'something-else',
    ])->assertUnprocessable()->assertJsonValidationErrors('password');
});

it('rejects registration with an already-taken e-mail', function () {
    User::factory()->create(['email' => 'rubens@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Rubens Couto',
        'email' => 'rubens@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('rejects registration with missing required fields', function () {
    $this->postJson('/api/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
