<?php

namespace App\Filament\Pages\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Enums\Role;
use App\Filament\Forms\AturanFormRequest;
use App\Http\Requests\Auth\PortalLoginRequest;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Login Admin memakai aturan yang sama dengan portal: validasi & pesan dari PortalLoginRequest,
 * autentikasi lewat AuthenticateUserAction (pesan umum, 3 kegagalan → 15 menit),
 * tanpa "Ingat saya" (PRD 4.2–4.5, 22.2).
 */
class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        try {
            app(AuthenticateUserAction::class)->handle($data['email'], $data['password'], (string) request()->ip(), Role::Admin);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(['data.email' => $exception->errors()['email']]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        $request = new PortalLoginRequest;

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
