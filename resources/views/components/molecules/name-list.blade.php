@props([
    'names',
    'empty' => 'Belum ada data',
])

{{--
    Daftar nama dalam satu rangkaian teks dipisah koma (PRD 8.3): membungkus ke baris berikutnya,
    tidak dipotong, tanpa ellipsis.
--}}
@if ($names === [])
    <span {{ $attributes->class(['text-ink-muted italic']) }}>{{ $empty }}</span>
@else
    <p {{ $attributes->class(['leading-relaxed text-pretty break-words']) }}>{{ \App\Support\Format::daftarNama($names) }}</p>
@endif
