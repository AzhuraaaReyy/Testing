{{-- View untuk App\View\Components\Organisms\Sidebar. @var list<\App\DTOs\Navigation\NavGroupData> $groups --}}

{{-- Latar gelap di balik drawer mobile. --}}
<div class="fixed inset-0 z-30 bg-ink/45 lg:hidden" hidden data-drawer-backdrop></div>

<aside
    id="sidebar"
    aria-label="Navigasi utama"
    class="app-sidebar flex shrink-0 flex-col border-r border-line bg-surface max-lg:shadow-overlay"
>
    {{-- Kepala drawer (mobile saja). --}}
    <div class="flex h-(--navbar-height) items-center justify-between gap-2 border-b border-line px-4 lg:hidden">
        <x-molecules.brand />
        <button
            type="button"
            class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-md text-ink-muted transition-colors duration-150 hover:bg-subtle hover:text-ink"
            aria-label="Tutup menu navigasi"
            data-drawer-close
        >
            <x-atoms.icon :icon="\App\Enums\Icon::X" />
        </button>
    </div>

    <nav class="scrollbar-none flex-1 overflow-y-auto px-3 py-4">
        @foreach ($groups as $group)
            <div @class(['mt-5' => ! $loop->first])>
                @if ($group->label)
                    <p class="nav-group-label mb-1.5 px-3 text-[0.6875rem] font-semibold uppercase tracking-[0.12em] text-ink-muted">{{ $group->label }}</p>
                @endif
                <ul class="flex flex-col gap-0.5">
                    @foreach ($group->items as $item)
                        <li><x-molecules.nav-link :item="$item" /></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>
