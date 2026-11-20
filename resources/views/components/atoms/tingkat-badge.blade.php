@props(['tingkat'])

{{-- Tingkat ditampilkan sebagai label teks, bukan warna saja, agar tetap terbaca tanpa persepsi warna. --}}
<x-atoms.label-badge {{ $attributes }}>{{ $tingkat->getLabel() }}</x-atoms.label-badge>
