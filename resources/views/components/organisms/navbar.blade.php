<header class="sticky top-0 z-30 flex h-(--navbar-height) items-center gap-2 border-b border-line bg-surface/95 px-3 backdrop-blur-sm sm:px-4">
    {{-- Mobile: buka drawer (PRD 17.3). --}}
    <button
        type="button"
        class="grid size-11 cursor-pointer place-items-center rounded-md text-ink-muted transition-colors duration-150 hover:bg-subtle hover:text-ink lg:hidden"
        aria-label="Buka menu navigasi"
        aria-controls="sidebar"
        aria-expanded="false"
        data-drawer-open
    >
        <x-atoms.icon :icon="\App\Enums\Icon::Menu" />
    </button>

    {{-- Desktop: ciutkan/lebarkan Sidebar (PRD 17.2). --}}
    <button
        type="button"
        class="hidden size-11 cursor-pointer place-items-center rounded-md text-ink-muted transition-colors duration-150 hover:bg-subtle hover:text-ink lg:grid"
        aria-label="Ciutkan sidebar"
        aria-controls="sidebar"
        aria-expanded="true"
        data-sidebar-toggle
    >
        <x-atoms.icon :icon="\App\Enums\Icon::PanelLeft" />
    </button>

    <x-molecules.brand class="ml-1" />

    <div class="ml-auto">
        <x-organisms.account-menu />
    </div>
</header>
