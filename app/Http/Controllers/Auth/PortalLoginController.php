<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticatePortalUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PortalLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PortalLoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(PortalLoginRequest $request, AuthenticatePortalUserAction $authenticate): RedirectResponse
    {
        $authenticate->handle($request->email(), $request->password(), (string) $request->ip());

        $request->session()->regenerate();

        // Kembali ke halaman yang sebelumnya diminta jika masih valid (PRD 4.6 poin 4).
        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda berhasil keluar dari sistem.');
    }
}
