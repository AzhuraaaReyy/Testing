<?php

namespace App\Services;

use App\DTOs\Navigation\NavGroupData;
use App\DTOs\Navigation\NavItemData;
use App\Enums\Icon;
use App\Enums\JenisLaporan;
use App\Enums\Role;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Satu sumber struktur menu Sidebar untuk halaman Blade, sesuai peran pengguna.
 * Item yang rutenya belum terdaftar (mis. Resource Filament yang belum dibuat) disembunyikan,
 * sehingga menu otomatis lengkap begitu rute tersebut tersedia.
 */
final class NavigationService
{
    /**
     * @return list<NavGroupData>
     */
    public function groups(Request $request, User $user): array
    {
        $groups = [];

        foreach ($this->definition($user->role) as $label => $items) {
            $resolved = [];

            foreach ($items as [$itemLabel, $icon, $routeName, $parameters]) {
                if (Route::has($routeName)) {
                    $resolved[] = new NavItemData(
                        label: $itemLabel,
                        icon: $icon,
                        url: route($routeName, $parameters),
                        active: $this->isActive($request, $routeName, $parameters),
                    );
                }
            }

            if ($resolved !== []) {
                $groups[] = new NavGroupData($label === '' ? null : $label, $resolved);
            }
        }

        return $groups;
    }

    /**
     * Struktur menu: label grup => daftar [label, ikon, nama rute, parameter rute].
     * Grup tanpa label memakai kunci ''.
     *
     * @return array<string, list<array{string, Icon, string, array<string, string>}>>
     */
    private function definition(Role $role): array
    {
        $laporan = array_map(
            fn (JenisLaporan $jenis): array => [$jenis->labelMenu($role), $jenis->icon(), $role->laporanRoute(), ['jenis' => $jenis->value]],
            JenisLaporan::cases(),
        );

        $beranda = [[$role->labelBeranda(), Icon::LayoutDashboard, $role->homeRoute(), []]];

        if ($role !== Role::Admin) {
            // Portal Guru/Siswa: tanpa menu CRUD (PRD v1.3).
            return [
                '' => $beranda,
                $role->labelGrupLaporan() => $laporan,
            ];
        }

        return [
            '' => $beranda,
            'Data Master' => [
                ['Siswa', Icon::Users, 'filament.admin.resources.siswa.index', []],
                ['Kelas', Icon::Building, 'filament.admin.resources.kelas.index', []],
                ['Guru', Icon::Presentation, 'filament.admin.resources.guru.index', []],
            ],
            $role->labelGrupLaporan() => $laporan,
        ];
    }

    /**
     * @param  array<string, string>  $parameters
     */
    private function isActive(Request $request, string $routeName, array $parameters): bool
    {
        $route = $request->route();

        if ($route === null || ! $request->routeIs($routeName)) {
            return false;
        }

        foreach ($parameters as $key => $value) {
            $current = $route->parameter($key);

            if (($current instanceof BackedEnum ? $current->value : $current) !== $value) {
                return false;
            }
        }

        return true;
    }
}
