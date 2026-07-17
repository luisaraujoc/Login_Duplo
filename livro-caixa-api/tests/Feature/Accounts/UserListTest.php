<?php

use App\Models\User;

it('lists every registered user for the invite picker', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    User::factory()->count(3)->create();

    $this->getJson('/api/users')->assertOk()->assertJsonCount(4, 'data');
});

it('blocks the user list for a guest', function () {
    $this->getJson('/api/users')->assertUnauthorized();
});
