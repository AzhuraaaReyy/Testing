<?php

use App\DTOs\Navigation\NavGroupData;
use App\DTOs\Navigation\NavItemData;
use App\Enums\JenisLaporan;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

function requestFor(string $url): Request
{
    $request = Request::create($url);
    $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request)->bind($request));

    return $request;
}

/**
 * @return Collection<int, NavItemData>
 */
function navItems(Request $request, User $user): Collection
{
    return collect(app(NavigationService::class)->groups($request, $user))
        ->flatMap(fn (NavGroupData $group): array => $group->items);
}

it('menandai hanya item laporan yang sedang dibuka sebagai aktif', function () {
    $url = route('portal.laporan.show', JenisLaporan::GuruPerKelas);

    $active = navItems(requestFor($url), User::factory()->siswa()->make())
        ->filter(fn (NavItemData $item): bool => $item->active)
        ->pluck('url')
        ->all();

    expect($active)->toBe([$url]);
});

it('memberi menu portal tanpa CRUD dengan nama sesuai sudut pandang peran', function (string $state, string $grup, array $menu) {
    $user = User::factory()->{$state}()->make();
    $groups = app(NavigationService::class)->groups(Request::create('/'), $user);
    $items = navItems(Request::create('/'), $user);

    expect(collect($groups)->pluck('label')->all())->toBe([null, $grup])
        ->and($items->pluck('label')->all())->toBe($menu)
        ->and($items->pluck('url')->all())->each->toContain('/portal');
})->with([
    'siswa' => ['siswa', 'Kelas Saya', ['Beranda', 'Teman Sekelas', 'Guru Pengajar', 'Ringkasan Kelas']],
    'guru' => ['guru', 'Kelas yang Saya Ajar', ['Beranda', 'Siswa Saya', 'Rekan Pengajar', 'Ringkasan Kelas']],
]);

it('membentuk inisial pengguna dari maksimal dua kata pertama', function (string $nama, string $inisial) {
    expect((new User(['name' => $nama]))->inisial)->toBe($inisial);
})->with([
    ['Admin Sekolah', 'AS'],
    ['siti', 'S'],
    ['Budi Santoso Wijaya', 'BS'],
]);
