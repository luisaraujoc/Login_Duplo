<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Http\Resources\UserResource;
use App\Models\Account;
use App\Models\User;
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
        $this->authorizeMembership($request, $account);

        $request->session()->put('active_account_id', $account->id);

        return new AccountResource($account);
    }

    /**
     * List the users who have access to this account (name/email/role) —
     * shown alongside the "convidar alguém" picker.
     */
    public function users(Request $request, Account $account): AnonymousResourceCollection
    {
        $this->authorizeMembership($request, $account);

        return UserResource::collection($account->users()->orderBy('name')->get());
    }

    /**
     * Grant an existing user access to this account. There is no invite
     * e-mail/token flow — the app runs locally for a small group of known
     * people, so simply picking a user from the full list is enough.
     */
    public function inviteUser(Request $request, Account $account): AnonymousResourceCollection
    {
        $this->authorizeMembership($request, $account);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        abort_if(
            $account->users()->whereKey($data['user_id'])->exists(),
            422,
            'Esse usuário já tem acesso a esta conta.'
        );

        $account->users()->attach($data['user_id'], ['role' => $data['role'] ?? 'member']);

        return UserResource::collection($account->users()->orderBy('name')->get());
    }

    /**
     * Revoke a user's access to this account. Blocked when it's the last
     * remaining user, so an account can never end up with nobody able to
     * open it.
     */
    public function removeUser(Request $request, Account $account, User $user): AnonymousResourceCollection
    {
        $this->authorizeMembership($request, $account);

        abort_if(
            $account->users()->count() <= 1,
            422,
            'Não é possível remover o último usuário com acesso a esta conta.'
        );

        $account->users()->detach($user);

        return UserResource::collection($account->users()->orderBy('name')->get());
    }

    private function authorizeMembership(Request $request, Account $account): void
    {
        abort_unless(
            $request->user()->accounts()->whereKey($account->id)->exists(),
            403,
            'Você não tem acesso a esta conta.'
        );
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
