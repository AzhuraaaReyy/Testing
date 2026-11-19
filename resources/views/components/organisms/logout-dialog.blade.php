{{-- View untuk App\View\Components\Organisms\LogoutDialog. Konfirmasi logout (PRD 4.7). --}}
<dialog
    id="dialog-logout"
    aria-labelledby="dialog-logout-judul"
    class="m-auto w-[calc(100%-2rem)] max-w-md rounded-lg border border-line bg-surface p-0 text-ink shadow-overlay"
>
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-6 p-6">
        @csrf
        <div class="flex gap-4">
            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-danger-soft text-danger">
                <x-atoms.icon :icon="\App\Enums\Icon::LogOut" />
            </span>
            <div>
                <h2 id="dialog-logout-judul" class="text-lg font-semibold">Keluar dari sistem</h2>
                <p class="mt-1 text-base text-ink-muted">Apakah Anda yakin ingin keluar dari sistem?</p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-atoms.button data-dialog-close autofocus>Batal</x-atoms.button>
            <x-atoms.button type="submit" variant="danger">Ya, Logout</x-atoms.button>
        </div>
    </form>
</dialog>
