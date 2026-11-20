<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\Enums\PintuLogin;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PortalLoginController extends Controller
{
    /**
     * $adminAktif: Admin masih login di browser ini (lihat RedirectIfAuthenticatedPortal);
     * form diganti penjelasan + tombol logout.
     */
    public function create(Request $request): View
    {
        return view('pages.auth.login', ['adminAktif' => $request->user()?->role === Role::Admin]);
    }

    public function store(LoginRequest $request, AuthenticateUserAction $authenticate): RedirectResponse
    {
        $authenticate->handle($request->email(), $request->password(), (string) $request->ip(), PintuLogin::Portal);

        $request->session()->regenerate();

        // Kembali ke halaman yang sebelumnya diminta jika masih valid (PRD 4.6 poin 4).
        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(LogoutUserAction $logout): RedirectResponse
    {
        $logout->handle();

        return redirect()->route('login')->with('status', LogoutUserAction::BERHASIL);
    }
}
