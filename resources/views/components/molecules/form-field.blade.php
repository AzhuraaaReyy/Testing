@props([
    'name',
    'label',
    'error' => null,
])

{{--
    Label + kontrol + pesan error tepat di bawah field (PRD 14.7).
    Slot berisi kontrolnya; id kontrol = $name, pesan error = "{$name}-error".
    [data-field-error] juga dipakai validasi sisi klien (resources/js/components/login-form.ts).
--}}
<div {{ $attributes->class(['flex flex-col gap-1.5']) }} data-field="{{ $name }}">
    <label for="{{ $name }}" class="text-sm font-semibold text-ink">{{ $label }}</label>

    {{ $slot }}

    <p id="{{ $name }}-error" class="text-sm text-danger" data-field-error @if (! $error) hidden @endif>{{ $error }}</p>
</div>
