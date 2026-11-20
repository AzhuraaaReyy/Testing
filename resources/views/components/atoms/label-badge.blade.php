{{--
    Label kecil netral (Tingkat, nama Kelas). Satu sumber gaya untuk Laporan/portal dan panel Admin
    (App\Filament\LabelBadge), agar tampilannya identik di seluruh aplikasi.
--}}
<span {{ $attributes->class(['inline-flex min-w-9 justify-center rounded border border-line-strong bg-surface px-1.5 py-0.5 text-xs font-semibold tracking-wide whitespace-nowrap text-ink']) }}>
    {{ $slot }}
</span>
