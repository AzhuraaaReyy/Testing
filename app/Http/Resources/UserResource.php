<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Akun yang sedang login (GET /api/v1/me, respons login).
 *
 * Skema OpenAPI: docs/api/openapi.yaml → components.schemas.User.
 *
 * @mixin User
 */
final class UserResource extends JsonResource
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
            'role' => $this->role->value,
            'siswa_id' => $this->siswa_id,
            'guru_id' => $this->guru_id,
        ];
    }
}
