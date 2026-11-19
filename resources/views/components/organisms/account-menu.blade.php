{{--
    View untuk App\View\Components\Organisms\AccountMenu. @var \App\Models\User $user
    Dropdown akun Admin (PRD 17.1): Nama, Email, Logout. Tidak ada pengelolaan profil.
--}}
<div class="relative" data-disclosure>
    <button
        type="button"
        class="flex min-h-11 cursor-pointer items-center gap-2.5 rounded-md py-1 pr-2 pl-1 transition-colors duration-150 hover:bg-subtle"
        aria-expanded="false"
        aria-controls="menu-akun"
        data-disclosure-trigger
    >
        <span class="grid size-8 place-items-center rounded-full bg-primary-soft text-xs font-bold text-primary-ink" aria-hidden="true">{{ $user->inisial }}</span>
        <span class="hidden max-w-40 truncate text-sm font-semibold text-ink sm:block">{{ $user->name }}</span>
        <span class="sr-only sm:hidden">Menu akun {{ $user->name }}</span>
        <x-atoms.icon :icon="\App\Enums\Icon::ChevronDown" class="size-4 text-ink-muted" />
    </button>

    <div
        id="menu-akun"
        hidden
        data-disclosure-panel
        class="absolute top-full right-0 z-40 mt-2 w-64 overflow-hidden rounded-lg border border-line bg-surface shadow-overlay"
    >
        <div class="border-b border-line px-4 py-3">
            <p class="truncate text-sm font-semibold text-ink">{{ $user->name }}</p>
            <p class="truncate text-sm text-ink-muted">{{ $user->email }}</p>
        </div>
        <div class="p-1.5">
            <button
                type="button"
                class="flex min-h-11 w-full cursor-pointer items-center gap-2.5 rounded-md px-2.5 text-left text-sm font-semibold text-danger transition-colors duration-150 hover:bg-danger-soft"
                data-dialog-open="dialog-logout"
            >
                <x-atoms.icon :icon="\App\Enums\Icon::LogOut" class="size-4" />
                Logout
            </button>
        </div>
    </div>
</div>
