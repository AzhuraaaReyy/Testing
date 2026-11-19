<?php

namespace App\Http\Controllers;

use App\Actions\Laporan\GetLaporanKelasAction;
use App\Enums\JenisLaporan;
use App\Http\PartialResponse;
use App\Http\Requests\LaporanRequest;
use App\Models\User;
use App\Services\KelasAccessService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Satu controller untuk laporan Admin (/laporan) dan portal Guru/Siswa (/portal/laporan).
 * Cakupan Kelas ditentukan oleh peran pengguna, bukan oleh rute.
 */
final class LaporanController extends Controller
{
    public function __invoke(
        LaporanRequest $request,
        JenisLaporan $jenis,
        #[CurrentUser] User $user,
        KelasAccessService $access,
        GetLaporanKelasAction $getLaporan,
    ): Response|RedirectResponse {
        $kelas = $getLaporan->handle($jenis, $request->halaman(), $access->kelasIdsFor($user));

        // Halaman di luar jangkauan diarahkan ke halaman terakhir yang valid (PRD 14.8).
        if ($kelas->currentPage() > $kelas->lastPage()) {
            return redirect()->to($kelas->url($kelas->lastPage()));
        }

        /** @var View $view */
        $view = view('pages.laporan.show', [
            'jenis' => $jenis,
            'role' => $user->role,
            'kelas' => $kelas,
        ]);

        $content = $request->memintaFragmenTabel()
            ? $view->fragment(PartialResponse::TABEL)
            : $view->render();

        return response($content)->setVary(PartialResponse::HEADER);
    }
}
