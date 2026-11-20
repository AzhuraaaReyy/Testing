@props([
    'paginator',
    // Diterima dari Filament tetapi sengaja tidak dipakai: tanpa pilihan jumlah data per halaman (PRD 15.2).
    'pageOptions' => [],
    'currentPageOptionProperty' => 'tableRecordsPerPage',
    'extremeLinks' => false,
    'loadingTargets' => [],
])

{{--
    Override <x-filament::pagination> (tabel CRUD & Laporan di panel Admin): memakai komponen
    pagination yang sama dengan portal agar sesuai PRD 15.2–15.4 — Sebelumnya/Berikutnya tetap
    terlihat (redup) saat nonaktif, label "Berikutnya", angka "1.050", dan seluruh elemen tetap
    tampil di mobile. Aplikasi hanya memakai LengthAwarePaginator.
--}}
{{-- data-table-pagination: disembunyikan saat error state tabel (resources/css/filament/admin/theme.css). --}}
<x-molecules.pagination :paginator="$paginator" :livewire="true" data-table-pagination {{ $attributes }} />
