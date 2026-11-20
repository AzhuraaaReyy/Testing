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

/**
 * Struktur menu Sidebar portal Guru/Siswa (Blade), dengan nama menu sesuai sudut pandang peran.
 * Menu Admin dikelola panel Filament (Resource & App\Filament\Pages\Laporan).
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
            $groups[] = new NavGroupData($label === '' ? null : $label, array_map(
                fn (array $item): NavItemData => new NavItemData(
                    label: $item[0],
                    icon: $item[1],
                    url: route($item[2], $item[3]),
                    active: $this->isActive($request, $item[2], $item[3]),
                ),
                $items,
            ));
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
        // Portal Guru/Siswa: tanpa menu CRUD (PRD v1.3).
        return [
            '' => [['Beranda', Icon::LayoutDashboard, 'portal.dashboard', []]],
            $role->labelGrupLaporan() => array_map(
                fn (JenisLaporan $jenis): array => [$jenis->labelMenu($role), $jenis->icon(), 'portal.laporan.show', ['jenis' => $jenis->value]],
                JenisLaporan::cases(),
            ),
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
