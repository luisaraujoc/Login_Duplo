<?php

use App\Models\Category;
use App\Models\User;

it('lists categories without needing an active account (they are global)', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Category::factory()->count(3)->create();

    $this->getJson('/api/categories')->assertOk()->assertJsonCount(3, 'data');
});

it('creates a category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson('/api/categories', ['name' => 'Salário', 'type' => 'credit'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Salário')
        ->assertJsonPath('data.type', 'credit');

    expect(Category::query()->where('name', 'Salário')->exists())->toBeTrue();
});

it('rejects a category with an invalid type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->postJson('/api/categories', ['name' => 'Salário', 'type' => 'invalido'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('type');
});

it('updates a category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $category = Category::factory()->debit()->create(['name' => 'Antigo Nome']);

    $this->putJson("/api/categories/{$category->id}", ['name' => 'Novo Nome', 'type' => 'credit'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Novo Nome')
        ->assertJsonPath('data.type', 'credit');

    expect($category->refresh()->name)->toBe('Novo Nome');
});

it('deletes a category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $category = Category::factory()->create();

    $this->deleteJson("/api/categories/{$category->id}")->assertNoContent();

    expect(Category::query()->find($category->id))->toBeNull();
});

it('blocks category routes for a guest', function () {
    $category = Category::factory()->create();

    $this->getJson('/api/categories')->assertUnauthorized();
    $this->postJson('/api/categories', ['name' => 'X', 'type' => 'credit'])->assertUnauthorized();
    $this->putJson("/api/categories/{$category->id}", ['name' => 'X', 'type' => 'credit'])->assertUnauthorized();
    $this->deleteJson("/api/categories/{$category->id}")->assertUnauthorized();
});
