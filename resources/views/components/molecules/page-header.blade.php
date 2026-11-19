@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<header {{ $attributes->class(['flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-primary">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-balance text-ink sm:text-[1.75rem]">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 text-base text-ink-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
