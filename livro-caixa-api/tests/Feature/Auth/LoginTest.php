<?php

use App\Models\User;

it('logs in with correct credentials', function () {
    User::factory()->create([
        'email' => 'rubens@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'rubens@example.com',
        'password' => 'password123',
    ])->assertOk()->assertJsonPath('data.email', 'rubens@example.com');

    $this->getJson('/api/me')->assertOk()->assertJsonPath('data.email', 'rubens@example.com');
});

it('rejects an incorrect password', function () {
    User::factory()->create([
        'email' => 'rubens@example.com',
        'password' => 'password123',
    ]);

    $this->postJson('/api/login', [
        'email' => 'rubens@example.com',
        'password' => 'wrong-password',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('rejects login for an unknown e-mail', function () {
    $this->postJson('/api/login', [
        'email' => 'nobody@example.com',
        'password' => 'password123',
    ])->assertUnprocessable();
});

it('blocks /api/me for a guest', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});
