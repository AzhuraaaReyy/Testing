@props([
    'invalid' => false,
])

{{-- Input teks dasar. Penanda invalid visual + aria-invalid (PRD 14.7). --}}
<input
    {{ $attributes->merge(['type' => 'text'])->class([
        'block min-h-11 w-full rounded-md border bg-surface px-3.5 text-base text-ink transition-colors duration-150 placeholder:text-ink-muted/70 focus:outline-2 focus:outline-offset-0',
        'border-line-strong focus:border-primary focus:outline-primary/25' => ! $invalid,
        'border-danger focus:outline-danger/25' => $invalid,
    ]) }}
    @if ($invalid) aria-invalid="true" @endif
>
