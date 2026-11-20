<?php

namespace App\Filament\Widgets;

use App\Actions\Dashboard\GetRingkasanSekolahAction;
use App\DTOs\Dashboard\RingkasanSekolahData;
use App\Filament\Resources\Guru\GuruResource;
use App\Filament\Resources\Kelas\KelasResource;
use App\Filament\Resources\Siswa\SiswaResource;
use Filament\Widgets\Widget;

/**
 * Tiga kartu ringkasan Dashboard Admin (PRD 11.3). Memakai komponen Blade yang sama dengan
 * Dashboard portal (organisms/ringkasan-sekolah); di sini kartu menjadi shortcut ke CRUD.
 */
class RingkasanSekolahWidget extends Widget
{
    protected string $view = 'filament.widgets.ringkasan-sekolah';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array{ringkasan: RingkasanSekolahData, links: array{siswa: string, kelas: string, guru: string}}
     */
    protected function getViewData(): array
    {
        return [
            'ringkasan' => app(GetRingkasanSekolahAction::class)->handle(),
            'links' => [
                'siswa' => SiswaResource::getUrl(),
                'kelas' => KelasResource::getUrl(),
                'guru' => GuruResource::getUrl(),
            ],
        ];
    }
}
