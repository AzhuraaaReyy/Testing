@props([
    'title',
    'id' => null,
])

@php($headingId = $id ?? 'tabel-'.\Illuminate\Support\Str::slug($title))

{{-- Judul tabel selalu terpisah dari header kolom (PRD 15.1) dan tetap terlihat di semua state. --}}
<section aria-labelledby="{{ $headingId }}" {{ $attributes->class(['overflow-hidden rounded-lg border border-line bg-surface shadow-raised']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
        <h2 id="{{ $headingId }}" class="text-base font-semibold text-ink">{{ $title }}</h2>
        @isset($actions)
            <div class="flex items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>

    {{ $slot }}
</section>
