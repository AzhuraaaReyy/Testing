<?php

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Enums\PintuLogin;
use App\Enums\Role;
use App\Filament\Forms\AturanFormRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Login Admin memakai aturan yang sama dengan portal: validasi & pesan dari LoginRequest,
 * autentikasi lewat AuthenticateUserAction (pesan umum, 3 kegagalan → 15 menit),
 * tanpa "Ingat saya" (PRD 4.2–4.5, 22.2).
 */
class Login extends BaseLogin
{
    /** Kunci flash pesan informasi di portal (layouts/app). */
    public const FLASH_INFO = 'info';

    /**
     * Guru/Siswa yang sedang login membuka /admin/login: arahkan ke portal dengan penjelasan,
     * bukan ke /admin yang pasti 403 (PRD 22.2: akun Guru/Siswa tidak dapat mengakses panel Admin).
     */
    public function mount(): void
    {
        $user = Filament::auth()->user();

        if ($user instanceof User && $user->role !== Role::Admin) {
            session()->flash(self::FLASH_INFO, $user->role->pesanSudahLogin());
            $this->redirectRoute($user->role->homeRoute());

            return;
        }

        parent::mount();
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        try {
            app(AuthenticateUserAction::class)->handle($data['email'], $data['password'], (string) request()->ip(), PintuLogin::Admin);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(['data.email' => $exception->errors()['email']]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        $request = new LoginRequest;

        return $schema->components(AturanFormRequest::terapkan([
            TextInput::make('email')
                ->label('Email')
                ->type('email')
                ->autocomplete('off')
                ->autofocus(),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->autocomplete('off'),
        ], fn (): array => $request->rules(), $request->messages()));
    }
}
