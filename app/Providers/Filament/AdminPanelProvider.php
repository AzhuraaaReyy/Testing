<?php

namespace App\Providers\Filament;

use App\Actions\Auth\LogoutUserAction;
use App\Enums\Role;
use App\Filament\AvatarProviders\InisialAvatarProvider;
use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\PreventSensitiveCaching;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Form;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Component;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Default bersama seluruh tabel & aksi panel, agar tiap Resource tidak mengulanginya.
     */
    public function boot(): void
    {
        // Label & notifikasi aksi (Tambah/Detail/Edit/Hapus/Pulihkan) diatur lewat terjemahan:
        // lang/vendor/filament-actions/id dan lang/vendor/filament-panels/id.

        // 10 data per halaman tanpa pilihan jumlah (PRD 15.2); satu opsi = pemilih disembunyikan.
        Table::configureUsing(fn (Table $table): Table => $table->paginated([10]));

        // Konfirmasi yang menjelaskan akibatnya, bukan "Apakah Anda yakin ingin melakukan ini?" (PRD 14.3–14.4).
        DeleteAction::configureUsing(fn (DeleteAction $action): DeleteAction => $action
            ->modalDescription('Data akan dipindahkan ke Trash dan dapat dipulihkan kembali.'));
        RestoreAction::configureUsing(fn (RestoreAction $action): RestoreAction => $action
            ->modalDescription('Data akan dipulihkan dari Trash dan kembali aktif.'));

        // Setelah tambah, Admin kembali ke daftar (PRD 5.4): tanpa "Simpan & tambah lainnya".
        CreateRecord::disableCreateAnother();

        // Matikan validasi bawaan browser (pesannya mengikuti bahasa browser); seluruh pesan
        // validasi berasal dari FormRequest dalam Bahasa Indonesia (PRD 14.7).
        Form::configureUsing(fn (Form $form): Form => $form->extraAttributes(['novalidate' => true], merge: true));
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Tema memakai resources/css/tokens.css yang sama dengan halaman Blade.
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(Login::class)
            ->brandName(config('sekolah.nama_sistem'))
            // Logo/placeholder + nama sistem/sekolah, komponen yang sama dengan Navbar portal (PRD 17.1).
            ->brandLogo(fn (): Htmlable => new HtmlString(Blade::render('<x-molecules.brand :link="false" />')))
            ->brandLogoHeight('2.25rem')
            // Roboto di-self-host oleh Vite (vite.config.js → fonts), sama dengan portal: tidak ada
            // permintaan ke fonts.bunny.net (privasi, CSP, dan alert ZAP "SRI Attribute Missing").
            ->font('Roboto', provider: LocalFontProvider::class)
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): Htmlable => Vite::fonts())
            // Avatar inisial dibuat lokal (SVG), bukan dari ui-avatars.com (SEC2-08).
            ->defaultAvatarProvider(InisialAvatarProvider::class)
            ->colors([
                // Senada dengan token --color-primary di tokens.css.
                'primary' => Color::Teal,
            ])
            // Token desain hanya punya mode terang.
            ->darkMode(false)
            // Sidebar desktop terbuka default & dapat diciutkan jadi ikon (PRD 17.2).
            ->sidebarCollapsibleOnDesktop()
            // Pencarian di luar ruang lingkup (PRD 2.4 poin 5–6). Tanpa ini Filament mengaktifkan
            // pencarian global otomatis karena Resource memakai $recordTitleAttribute.
            ->globalSearch(false)
            ->navigationGroups(['Data Master', Role::Admin->labelGrupLaporan()])
            // Dropdown akun: Nama, Email, Logout dengan konfirmasi (PRD 4.7, 17.1).
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn (): View => view('filament.user-menu-email'))
            // "Guru atau Siswa? Masuk melalui portal" di bawah form login Admin.
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn (): View => view('filament.login-portal-hint'))
            // Session berakhir (419/401) & error state tabel "Coba Lagi" (PRD 4.6, 16.3).
            ->renderHook(PanelsRenderHook::BODY_END, fn (): View => view('filament.scripts'))
            ->userMenuItems([
                'logout' => fn (Action $action): Action => $action
                    ->url(null)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Keluar dari sistem')
                    ->modalDescription('Apakah Anda yakin ingin keluar dari sistem?')
                    ->modalSubmitActionLabel('Ya, Logout')
                    ->modalCancelActionLabel('Batal')
                    ->action(function (Component $livewire): void {
                        app(LogoutUserAction::class)->handle();

                        Notification::make()->success()->title(LogoutUserAction::BERHASIL)->send();

                        $livewire->redirect(Filament::getLoginUrl());
                    }),
            ])
            // Setelah tambah/edit kembali ke daftar (PRD 14.6); perubahan belum disimpan dikonfirmasi (PRD 14.5).
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->unsavedChangesAlerts()
            // Simpan profil + akun portal + relasi Kelas secara atomik.
            ->databaseTransactions()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                PreventSensitiveCaching::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
