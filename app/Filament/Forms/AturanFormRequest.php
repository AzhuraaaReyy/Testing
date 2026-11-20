<?php

namespace App\Filament\Forms;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use Livewire\Component as LivewireComponent;
use LogicException;

/**
 * Menghubungkan form Filament ke FormRequest, satu-satunya tempat aturan validasi (arsitektur:
 * validasi hanya di app/Http/Requests). Form Filament hanya mendefinisikan tampilan.
 *
 *   AturanFormRequest::terapkan([...field], fn (?Siswa $record) => SiswaRequest::aturan($record), SiswaRequest::pesan())
 *
 * $aturan dievaluasi oleh Filament per field, sehingga dapat menerima $record (record yang
 * sedang diedit, untuk pengecualian unik) dan $get (nilai field lain). Semua parameternya wajib
 * ber-default null: aturan elemen array ("field.*") dibaca sekali saat form dibangun.
 */
final class AturanFormRequest
{
    /**
     * @param  array<Component>  $komponen
     * @param  Closure(mixed ...): array<string, list<mixed>>  $aturan
     * @param  array<string, string>  $pesan  Format FormRequest::messages(): "field.rule" => pesan.
     * @return array<Component>
     */
    public static function terapkan(array $komponen, Closure $aturan, array $pesan = []): array
    {
        foreach ($komponen as $field) {
            if ($field instanceof Field) {
                self::pasang($field, $aturan, $pesan);
            }
        }

        return $komponen;
    }

    /**
     * Label field sebagai teks polos (label bisa berupa HTML) untuk nama field di pesan validasi.
     */
    private static function labelTeks(Field $field): string
    {
        $label = $field->getLabel();

        return $label instanceof Htmlable ? strip_tags($label->toHtml()) : ($label ?? $field->getName());
    }

    /**
     * @param  Closure(mixed ...): array<string, list<mixed>>  $aturan
     * @param  array<string, string>  $pesan
     */
    private static function pasang(Field $field, Closure $aturan, array $pesan): void
    {
        $nama = $field->getName();
        $aturanField = fn (Field $component): array => $component->evaluate($aturan)[$nama] ?? [];

        // "nama.rule" => pesan  menjadi  "rule" => pesan  untuk field ini.
        $pesanField = [];

        foreach ($pesan as $kunci => $isi) {
            if (str_starts_with($kunci, "{$nama}.")) {
                $pesanField[substr($kunci, strlen($nama) + 1)] = $isi;
            }
        }

        $field
            ->rules($aturanField)
            // Tanda * mengikuti aturan "required" (termasuk yang bersyarat).
            ->markAsRequired(fn (Field $component): bool => in_array('required', $aturanField($component), true))
            ->validationMessages($pesanField)
            // Nama field di pesan = label apa adanya. Bawaan Filament mengecilkan huruf pertama
            // (untuk kalimat Inggris), sehingga pesan Indonesia menjadi "nIS wajib diisi.".
            ->validationAttribute(fn (Field $component): string => self::labelTeks($component))
            // Validasi per field saat field ditinggalkan, dengan aturan yang sama (PRD 4.3, 14.7):
            // pesan muncul di bawah field tanpa menunggu tombol Simpan. Validasi penuh tetap saat submit.
            ->live(onBlur: true)
            ->afterStateUpdated(function (LivewireComponent $livewire, Field $component): void {
                $statePath = $component->getStatePath();

                // validateOnly() Livewire tidak membaca pesan khusus Filament (jalur submit membacanya),
                // jadi dikirim eksplisit agar pesan live & submit selalu sama.
                $pesan = [];

                foreach ($component->getValidationMessages() as $aturan => $isi) {
                    $pesan["{$statePath}.{$aturan}"] = $isi;
                }

                // Error dicatat tanpa melempar exception: exception akan menghentikan pemrosesan
                // pembaruan field lain di request yang sama (mis. ubah field lalu langsung klik Simpan).
                try {
                    $livewire->validateOnly($statePath, messages: $pesan);
                } catch (ValidationException $exception) {
                    foreach ($exception->errors() as $kunci => $daftarPesan) {
                        foreach ($daftarPesan as $isi) {
                            $livewire->addError($kunci, $isi);
                        }
                    }
                }
            });

        // Aturan per elemen array (mis. "kelas.*") tidak bergantung pada record.
        $aturanElemen = $aturan()["{$nama}.*"] ?? null;

        if ($aturanElemen === null) {
            return;
        }

        // Jangan diam-diam mengabaikan aturan elemen pada field yang tidak menampung array.
        if (! method_exists($field, 'nestedRecursiveRules')) {
            throw new LogicException("Field [{$nama}] tidak mendukung aturan elemen [{$nama}.*].");
        }

        $field->nestedRecursiveRules($aturanElemen);
    }
}
