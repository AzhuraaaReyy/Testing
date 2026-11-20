@props(['tingkat'])

{{-- Tingkat ditampilkan sebagai label teks, bukan warna saja, agar tetap terbaca tanpa persepsi warna. --}}
<span {{ $attributes->class(['inline-flex min-w-9 justify-center rounded border border-line-strong bg-surface px-1.5 py-0.5 text-xs font-semibold tracking-wide text-ink']) }}>
    {{ $tingkat->getLabel() }}
</span>
