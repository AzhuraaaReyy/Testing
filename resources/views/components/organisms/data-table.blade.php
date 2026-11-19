@props([
    'caption' => null,
])

{{--
    Tabel standar (PRD 15.1): header berlatar halus, baris lapang, garis pemisah tipis.
    Di layar kecil semua kolom dipertahankan dan dapat di-swipe horizontal tanpa scrollbar terlihat (PRD 17.5).
--}}
<div class="scrollbar-none overflow-x-auto overscroll-x-contain">
    <table {{ $attributes->class(['w-full border-collapse text-left text-[0.9375rem]']) }}>
        @if ($caption)
            <caption class="sr-only">{{ $caption }}</caption>
        @endif
        <thead class="bg-subtle">
            <tr class="border-b border-line">
                {{ $head }}
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            {{ $slot }}
        </tbody>
    </table>
</div>
