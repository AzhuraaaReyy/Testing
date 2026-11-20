<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PortalLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

/**
 * Autentikasi API dengan Bearer token (Laravel Sanctum). Aturan login sama dengan web:
 * pesan umum, 3 kegagalan → 15 menit, akun dengan profil di Trash ditolak (PRD 4.4–4.5, 22.2).
 */
final class AuthController extends Controller
{
    public function login(PortalLoginRequest $request, AuthenticateUserAction $authenticate): JsonResponse
    {
        $user = $authenticate->verifikasi(
            $request->email(),
            $request->password(),
            (string) $request->ip(),
            ...Role::cases(),
        );

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * Mencabut token yang sedang dipakai; token lain milik akun yang sama tetap berlaku.
     */
    public function logout(#[CurrentUser] User $user): JsonResponse
    {
        $user->currentAccessToken()->delete();

        return response()->json(['message' => LogoutUserAction::BERHASIL]);
    }

    public function me(#[CurrentUser] User $user): UserResource
    {
        return UserResource::make($user);
    }
}
