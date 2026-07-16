<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the account selected via AccountController::select() for this
 * session, scoping every account-bound route (books, movements, ...) to it.
 * Aborts with 409 rather than 403/404 — the request is well-formed and the
 * user is authenticated, they simply haven't picked a "conta" yet.
 */
class EnsureAccountSelected
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accountId = $request->session()->get('active_account_id');

        $account = $accountId
            ? $request->user()->accounts()->whereKey($accountId)->first()
            : null;

        abort_unless($account, 409, 'Nenhuma conta selecionada.');

        $request->attributes->set('current_account', $account);

        return $next($request);
    }
}
