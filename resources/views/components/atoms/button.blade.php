@props([
    'variant' => 'secondary',
    'href' => null,
    'icon' => null,
])

@php
    $classes = [
        'inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-md px-4 text-sm font-semibold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-50',
        match ($variant) {
            'primary' => 'bg-primary text-on-primary hover:bg-primary-hover',
            'danger' => 'bg-danger text-white hover:bg-danger-hover',
            'ghost' => 'text-ink-muted hover:bg-subtle hover:text-ink',
            default => 'border border-line-strong bg-surface text-ink shadow-raised hover:bg-subtle',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<x-atoms.icon :icon="$icon" class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon)<x-atoms.icon :icon="$icon" class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
