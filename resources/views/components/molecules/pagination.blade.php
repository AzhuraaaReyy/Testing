{{-- View untuk App\View\Components\Molecules\Pagination. --}}
@php
    $control = 'inline-flex min-h-10 min-w-10 items-center justify-center rounded-md text-sm font-semibold tabular-nums transition-colors duration-150';
    $idle = 'cursor-pointer text-ink hover:bg-subtle';
    $disabled = 'cursor-not-allowed text-ink-muted opacity-45';

    // Mode Livewire: klik ditangani komponen (href tetap ada sebagai fallback tanpa JavaScript).
    // Mode biasa: data-page-link diperkaya resources/js/components/table-region.ts.
    $perilaku = fn (string $metode, ?int $nomor = null): string => $livewire
        ? 'wire:click.prevent="'.e($aksiLivewire($metode, $nomor)).'" wire:loading.attr="aria-disabled"'
        : 'data-page-link';
@endphp

<nav
    aria-label="Navigasi halaman"
    {{ $attributes->class(['flex flex-col gap-3 border-t border-line px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between']) }}
>
    <p class="text-sm text-ink-muted tabular-nums">{{ $ringkasan() }}</p>

    @if ($paginator->lastPage() > 1)
        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $control }} {{ $disabled }} gap-1 px-2.5" aria-disabled="true">
                        <x-atoms.icon :icon="\App\Enums\Icon::ChevronLeft" class="size-4" />
                        <span class="max-sm:sr-only">Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" {!! $perilaku('previousPage') !!} class="{{ $control }} {{ $idle }} gap-1 px-2.5">
                        <x-atoms.icon :icon="\App\Enums\Icon::ChevronLeft" class="size-4" />
                        <span class="max-sm:sr-only">Sebelumnya</span>
                    </a>
                @endif
            </li>

            @foreach ($halaman as $nomor)
                <li>
                    @if ($nomor === null)
                        <span class="{{ $control }} min-w-6 text-ink-muted" aria-hidden="true">…</span>
                    @elseif ($nomor === $paginator->currentPage())
                        <span class="{{ $control }} bg-primary text-on-primary" aria-current="page">
                            <span class="sr-only">Halaman </span>{{ $nomor }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($nomor) }}" {!! $perilaku('gotoPage', $nomor) !!} class="{{ $control }} {{ $idle }}">
                            <span class="sr-only">Halaman </span>{{ $nomor }}
                        </a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" {!! $perilaku('nextPage') !!} class="{{ $control }} {{ $idle }} gap-1 px-2.5">
                        <span class="max-sm:sr-only">Berikutnya</span>
                        <x-atoms.icon :icon="\App\Enums\Icon::ChevronRight" class="size-4" />
                    </a>
                @else
                    <span class="{{ $control }} {{ $disabled }} gap-1 px-2.5" aria-disabled="true">
                        <span class="max-sm:sr-only">Berikutnya</span>
                        <x-atoms.icon :icon="\App\Enums\Icon::ChevronRight" class="size-4" />
                    </span>
                @endif
            </li>
        </ul>
    @endif
</nav>
