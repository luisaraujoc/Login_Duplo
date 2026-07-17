<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'photo_path' => $this->photo_path,
            // Present only when this resource wraps a User loaded through
            // Account::users() (i.e. the account-members list), not the
            // global /users listing.
            'role' => $this->whenPivotLoaded('account_user', fn () => $this->pivot->role),
        ];
    }
}
