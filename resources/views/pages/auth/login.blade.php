{{--
    Login portal Guru/Siswa (PRD 4.2–4.5, v1.3). Payload = App\Http\Requests\Auth\LoginRequest.
    Tanpa "Ingat saya" dan tanpa pengisian otomatis kredensial (PRD 4.2 poin 5–7).
--}}
<x-layouts.guest title="Login">
    <section aria-labelledby="judul-login" class="rounded-lg border border-line bg-surface p-6 shadow-raised sm:p-8">
        <header class="flex flex-col gap-1.5">
            <h1 id="judul-login" class="text-2xl font-semibold tracking-tight text-ink">Masuk ke Portal</h1>
            <p class="text-base text-ink-muted">Untuk Guru dan Siswa. Gunakan email dan password dari Admin sekolah.</p>
        </header>

        @if (session('status'))
            <x-molecules.alert class="mt-5">{{ session('status') }}</x-molecules.alert>
        @endif

        @if (session(\App\Support\SesiLogin::FLASH))
            <x-molecules.alert variant="info" class="mt-5">{{ session(\App\Support\SesiLogin::FLASH) }}</x-molecules.alert>
        @endif

        <form method="POST" action="{{ route('login.store') }}" novalidate data-login-form class="mt-6 flex flex-col gap-5">
            @csrf

            <x-molecules.form-field name="email" label="Email" :error="$errors->first('email')">
                <x-atoms.input
                    type="email"
                    id="email"
                    name="email"
                    :value="old('email')"
                    :invalid="$errors->has('email')"
                    inputmode="email"
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    required
                    autofocus
                    aria-describedby="email-error"
                />
            </x-molecules.form-field>

            <x-molecules.form-field name="password" label="Password" :error="$errors->first('password')">
                <x-molecules.password-input name="password" :invalid="$errors->has('password')" autocomplete="off" required />
            </x-molecules.form-field>

            <button
                type="submit"
                class="mt-1 inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-md bg-primary px-4 text-base font-semibold text-on-primary transition-colors duration-150 hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-70"
                data-login-submit
            >
                <x-atoms.icon :icon="\App\Enums\Icon::LoaderCircle" class="size-4 animate-spin" data-loading-icon hidden />
                <span data-submit-label>Login</span>
            </button>
        </form>
    </section>

    <p class="text-center text-sm text-ink-muted">
        Admin sekolah?
        <a href="{{ route('filament.admin.auth.login') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Masuk melalui panel Admin</a>
    </p>
</x-layouts.guest>
