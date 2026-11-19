@props([
    'align' => 'left',
    'numeric' => false,
])

{{-- Kolom kuantitas rata kanan dengan angka tabular (PRD 15.1). --}}
<td
    {{ $attributes->class([
        'px-5 py-4 align-top text-ink',
        'text-right' => $align === 'right' || $numeric,
        'text-center' => $align === 'center',
        'tabular-nums' => $numeric,
    ]) }}
>{{ $slot }}</td>
