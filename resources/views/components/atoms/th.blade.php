@props([
    'align' => 'left',
])

<th
    scope="col"
    {{ $attributes->class([
        'px-5 py-3 text-xs font-semibold uppercase tracking-[0.06em] whitespace-nowrap text-ink-muted',
        'text-right' => $align === 'right',
        'text-center' => $align === 'center',
    ]) }}
>{{ $slot }}</th>
