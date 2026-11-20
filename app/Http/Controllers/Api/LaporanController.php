<?php

namespace App\Http\Controllers\Api;

use App\Actions\Laporan\GetLaporanKelasAction;
use App\Enums\JenisLaporan;
use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanRequest;
use App\Http\Resources\LaporanKelasResource;
use App\Models\User;
use App\Services\KelasAccessService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Tiga laporan (PRD 8–10, 22.4): 10 Kelas per halaman, setiap Kelas satu kali.
 * Admin: seluruh Kelas; Siswa: Kelasnya; Guru: Kelas yang diajar. Sama dengan web.
 */
final class LaporanController extends Controller
{
    public function __invoke(
        LaporanRequest $request,
        JenisLaporan $jenis,
        #[CurrentUser] User $user,
        KelasAccessService $access,
        GetLaporanKelasAction $getLaporan,
    ): AnonymousResourceCollection {
        $kelas = $getLaporan->handle($jenis, $request->halaman(), $access->kelasIdsFor($user));

        return LaporanKelasResource::collection($kelas)->additional([
            'jenis' => $jenis->value,
            'judul' => $jenis->judul($user->role),
        ]);
    }
}
