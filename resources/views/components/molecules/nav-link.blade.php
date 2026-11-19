{{-- @var \App\DTOs\Navigation\NavItemData $item --}}
@props(['item'])

<a
    href="{{ $item->url }}"
    title="{{ $item->label }}"
    @if ($item->active) aria-current="page" @endif
    data-nav-link
    @class([
        'nav-link relative flex min-h-11 items-center gap-3 rounded-md px-3 text-[0.9375rem] transition-colors duration-150',
        'bg-primary-soft font-semibold text-primary-ink before:absolute before:inset-y-2 before:left-0 before:w-[3px] before:rounded-full before:bg-primary' => $item->active,
        'text-ink-muted hover:bg-subtle hover:text-ink' => ! $item->active,
    ])
>
    <x-atoms.icon :icon="$item->icon" @class(['text-primary' => $item->active]) />
    <span class="nav-label truncate">{{ $item->label }}</span>
</a>
