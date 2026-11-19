@props([
    'fragment',
    'label',
])

{{--
    Wilayah tabel yang dapat dimuat ulang sebagian (resources/js/components/table-region.ts).
    Kontrak: lihat App\Http\PartialResponse dan resources/js/types/contracts.ts (FragmentRequest).
--}}
<div
    data-table-region
    data-state="ready"
    data-fragment-header="{{ \App\Http\PartialResponse::HEADER }}"
    data-fragment-name="{{ $fragment }}"
    aria-label="{{ $label }}"
    tabindex="-1"
    {{ $attributes->class(['table-region relative focus:outline-none']) }}
>
    <div class="table-region-progress absolute inset-x-0 top-0 z-10 h-0.5 overflow-hidden bg-primary-soft" aria-hidden="true"></div>
    <p class="sr-only" role="status" aria-live="polite" data-region-status></p>

    <div data-region-content>
        {{ $slot }}
    </div>

    <template data-region-error>
        <x-molecules.table-error />
    </template>
</div>
