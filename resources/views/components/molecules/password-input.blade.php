@props([
    'name',
    'invalid' => false,
])

{{-- Password tersembunyi secara default dengan tombol tampil/sembunyikan (PRD 4.3). --}}
<div class="relative" data-password-input>
    <x-atoms.input
        type="password"
        :id="$name"
        :name="$name"
        :invalid="$invalid"
        class="pr-12"
        aria-describedby="{{ $name }}-error"
        {{ $attributes }}
    />
    <button
        type="button"
        class="absolute inset-y-0 right-0 grid w-11 cursor-pointer place-items-center rounded-r-md text-ink-muted transition-colors duration-150 hover:text-ink"
        aria-label="Tampilkan password"
        aria-pressed="false"
        aria-controls="{{ $name }}"
        data-password-toggle
    >
        <x-atoms.icon :icon="\App\Enums\Icon::Eye" class="size-5" data-icon-show />
        <x-atoms.icon :icon="\App\Enums\Icon::EyeOff" class="size-5" data-icon-hide hidden />
    </button>
</div>
