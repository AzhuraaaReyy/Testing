<?php

namespace App\Filament;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * Badge Tingkat & Kelas di panel Admin memakai komponen Blade yang sama dengan Laporan
 * (<x-atoms.label-badge>), menggantikan badge berwarna bawaan Filament, sehingga warna dan
 * bentuknya konsisten di seluruh aplikasi.
 *
 *   TextColumn::make('tingkat')->formatStateUsing(LabelBadge::format(...))->html()
 */
final class LabelBadge
{
    public static function html(string $teks): HtmlString
    {
        return new HtmlString(Blade::render('<x-atoms.label-badge>{{ $teks }}</x-atoms.label-badge>', ['teks' => $teks]));
    }

    /** Untuk formatStateUsing(): enum ber-label (mis. Tingkat) atau teks biasa (mis. nama Kelas). */
    public static function format(HasLabel|string|null $state): ?HtmlString
    {
        $teks = $state instanceof HasLabel ? $state->getLabel() : $state;

        // Label berupa HTML (kontrak HasLabel mengizinkannya) ditampilkan sebagai teks polos.
        if ($teks instanceof Htmlable) {
            $teks = strip_tags($teks->toHtml());
        }

        return filled($teks) ? self::html($teks) : null;
    }
}
