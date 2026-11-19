@props([
    'label',
    'value',
    'icon',
    'href' => null,
])

{{--
    Kartu ringkasan Dashboard (PRD 11.3): ikon, label, jumlah; tanpa deskripsi tambahan.
    Dengan href, kartu menjadi shortcut (hover sedikit terangkat); tanpa href, kartu statis.
--}}
@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'flex items-center gap-4 rounded-lg border border-line bg-surface p-5 shadow-raised',
        'cursor-pointer transition duration-200 hover:-translate-y-0.5 hover:border-line-strong active:translate-y-0' => $href,
    ]) }}
>
    <span class="grid size-11 shrink-0 place-items-center rounded-md bg-primary-soft text-primary">
        <x-atoms.icon :icon="$icon" class="size-5" />
    </span>
    <span class="flex min-w-0 flex-col">
        <span class="text-sm font-semibold text-ink-muted">{{ $label }}</span>
        <span class="text-2xl font-semibold tabular-nums text-ink">{{ \App\Support\Format::angka($value) }}</span>
    </span>
</{{ $tag }}>
