<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DistribusiKelasWidget;
use App\Filament\Widgets\RingkasanSekolahWidget;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Dashboard Admin (PRD 11): kartu ringkasan, lalu distribusi Siswa, lalu distribusi Guru.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getSubheading(): string
    {
        return 'Ringkasan data Siswa, Kelas, dan Guru.';
    }

    public function getWidgets(): array
    {
        return [
            RingkasanSekolahWidget::class,
            DistribusiKelasWidget::make(['relasi' => 'siswa']),
            DistribusiKelasWidget::make(['relasi' => 'guru']),
        ];
    }

    public function getColumns(): int
    {
        return 1;
    }
}
