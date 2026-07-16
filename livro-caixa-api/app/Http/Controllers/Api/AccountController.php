<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    /**
     * List the accounts (ledgers) the authenticated user has access to —
     * this replaces the legacy free-text "account name" login step.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return AccountResource::collection(
            $request->user()->accounts()->orderBy('name')->get()
        );
    }

    /**
     * Create a new ledger account and attach the current user as its
     * owner — equivalent to the legacy sign-up_c.php, minus the account's
     * own password (ownership is the real access control now).
     */
    public function store(Request $request): AccountResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
        ]);

        $account = Account::query()->create($data);
        $account->users()->attach($request->user(), ['role' => 'owner']);

        return new AccountResource($account);
    }

    /**
     * Select the active account for this session. Equivalent to the
     * legacy "second login", minus the unused/unchecked account password.
     */
    public function select(Request $request, Account $account): AccountResource
    {
        abort_unless(
            $request->user()->accounts()->whereKey($account->id)->exists(),
            403,
            'Você não tem acesso a esta conta.'
        );

        $request->session()->put('active_account_id', $account->id);

        return new AccountResource($account);
    }

    /**
     * The currently selected account for this session, if any. Deliberately
     * outside the account.selected middleware group — its whole purpose is
     * to answer "none selected yet" without that middleware's 409 abort.
     */
    public function current(Request $request): JsonResponse|AccountResource
    {
        $accountId = $request->session()->get('active_account_id');

        $account = $accountId
            ? $request->user()->accounts()->whereKey($accountId)->first()
            : null;

        if (! $account) {
            return response()->json(['data' => null]);
        }

        return new AccountResource($account);
    }
}
