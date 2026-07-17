<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Sanctum's cookie/session auth (statefulApi()) only starts a session
     * and enables CSRF for requests it recognizes as coming from the
     * configured frontend — matched via the Origin/Referer header. Without
     * this, every $request->session() call in the app (account selection,
     * EnsureAccountSelected, login/logout) would blow up in tests with
     * "Session store not set on request", the same way it does for a bare
     * curl request with no Origin header.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $statefulDomain = config('sanctum.stateful')[0] ?? 'localhost:5173';

        $this->withHeader('Origin', "http://{$statefulDomain}");
    }
}
