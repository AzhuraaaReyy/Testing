@props([
    'variant' => 'success',
])

{{-- Pesan umpan balik singkat (mis. setelah logout). Diumumkan ke screen reader. --}}
<div
    role="status"
    {{ $attributes->class([
        'flex items-start gap-2.5 rounded-md border px-3.5 py-3 text-sm',
        'border-primary/30 bg-primary-soft text-primary-ink' => $variant === 'success',
        'border-danger/30 bg-danger-soft text-danger' => $variant === 'danger',
    ]) }}
>
    <x-atoms.icon :icon="$variant === 'danger' ? \App\Enums\Icon::TriangleAlert : \App\Enums\Icon::CircleCheck" class="mt-px size-4" />
    <p>{{ $slot }}</p>
</div>
