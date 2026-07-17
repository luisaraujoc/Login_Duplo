<?php

use App\Models\NfeLink;
use App\Models\User;

it('lists nfe links', function () {
    $this->actingAs(User::factory()->create());
    NfeLink::factory()->count(2)->create();

    $this->getJson('/api/nfe-links')->assertOk()->assertJsonCount(2, 'data');
});

it('creates an nfe link', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/nfe-links', ['url' => 'https://nfe.example.com/12345'])
        ->assertCreated()
        ->assertJsonPath('data.url', 'https://nfe.example.com/12345');
});

it('rejects an invalid url', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/nfe-links', ['url' => 'not-a-url'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('url');
});

it('deletes an nfe link', function () {
    $this->actingAs(User::factory()->create());
    $link = NfeLink::factory()->create();

    $this->deleteJson("/api/nfe-links/{$link->id}")->assertNoContent();

    expect(NfeLink::query()->find($link->id))->toBeNull();
});
