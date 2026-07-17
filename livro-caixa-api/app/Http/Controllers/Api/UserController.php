<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Global list of registered users, used to populate the "convidar alguém
 * para esta conta" picker. Fine to expose broadly for a locally-run,
 * family-scale app — would need tightening (search-only, no full listing)
 * before ever being deployed somewhere multi-tenant or public-facing.
 */
class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::query()->orderBy('name')->get());
    }
}
