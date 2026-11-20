<?php

namespace App\Http\Controllers\Api;

use App\Actions\Dashboard\GetRingkasanSekolahAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\RingkasanSekolahResource;

/**
 * Total Siswa, Kelas, dan Guru aktif (PRD 11.3, 22.3); sama untuk semua peran.
 */
final class RingkasanController extends Controller
{
    public function __invoke(GetRingkasanSekolahAction $ringkasan): RingkasanSekolahResource
    {
        return RingkasanSekolahResource::make($ringkasan->handle());
    }
}
