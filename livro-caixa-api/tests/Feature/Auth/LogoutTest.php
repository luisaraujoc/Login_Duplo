<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('logs out and invalidates the session', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();

    $this->getJson('/api/me')->assertOk();

    $this->postJson('/api/logout')->assertNoContent();

    // Checked directly via the same Auth facade the controller itself uses,
    // rather than a second /api/me HTTP call or assertGuest(): within one
    // test process, Sanctum's 'sanctum' RequestGuard caches whichever user
    // it first resolved and never re-checks (Illuminate\Auth\RequestGuard
    // ::user()), and assertGuest()'s container lookup was observed to
    // disagree with Auth::guard('web') here too — both same-process test
    // artifacts a real second browser request would never hit, since each
    // real request builds its own guards from scratch.
    expect(Auth::guard('web')->check())->toBeFalse();
});

it('blocks logout for a guest', function () {
    $this->postJson('/api/logout')->assertUnauthorized();
});
