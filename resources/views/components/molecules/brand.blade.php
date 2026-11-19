{{-- View untuk App\View\Components\Molecules\Brand. --}}
<a href="{{ $homeUrl }}" {{ $attributes->class(['flex min-w-0 items-center gap-3 rounded-md']) }}>
    @if ($logo)
        <img src="{{ asset($logo) }}" alt="" class="size-9 shrink-0 rounded-md object-contain">
    @else
        <span class="grid size-9 shrink-0 place-items-center rounded-md bg-primary text-sm font-semibold text-on-primary" aria-hidden="true">SM</span>
    @endif

    <span class="flex min-w-0 flex-col leading-tight">
        <span class="truncate text-[0.9375rem] font-semibold text-ink">{{ $namaSistem }}</span>
        @if ($namaSekolah)
            <span class="truncate text-xs text-ink-muted">{{ $namaSekolah }}</span>
        @endif
    </span>
</a>
