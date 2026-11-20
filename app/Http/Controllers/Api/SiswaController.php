<?php

namespace App\Http\Controllers\Api;

use App\Actions\DataMaster\SimpanSiswaAction;
use App\Http\Requests\DataMaster\PulihkanSiswaRequest;
use App\Http\Requests\DataMaster\SiswaRequest;
use App\Http\Resources\SiswaResource;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use LogicException;

/**
 * API CRUD Siswa (PRD 5). Validasi: SiswaRequest; penyimpanan: SimpanSiswaAction (sama dengan panel Admin).
 */
final class SiswaController extends DataMasterController
{
    public function store(SiswaRequest $request, SimpanSiswaAction $simpan): JsonResponse
    {
        return $this->dibuat($simpan->handle($request->validated()));
    }

    public function update(SiswaRequest $request, string $siswa, SimpanSiswaAction $simpan): JsonResource
    {
        return $this->tersimpan($simpan->handle($request->validated(), $this->siswa($this->aktif($siswa))));
    }

    /**
     * Wajib memilih satu Kelas aktif; Siswa tidak pernah aktif tanpa Kelas (PRD 5.7).
     */
    public function pulihkan(PulihkanSiswaRequest $request, string $siswa): JsonResource
    {
        $record = $this->siswa($this->diTrash($siswa));
        $record->pulihkanKeKelas($request->kelasId());

        return $this->dipulihkan($record);
    }

    /**
     * @return Builder<Siswa>
     */
    protected function query(): Builder
    {
        return Siswa::query()->with(['kelas', 'akun'])->orderBy('nama')->orderBy('id');
    }

    protected function resource(Siswa|Kelas|Guru $record): JsonResource
    {
        return SiswaResource::make($this->siswa($record));
    }

    protected function koleksi(LengthAwarePaginator $records): AnonymousResourceCollection
    {
        return SiswaResource::collection($records);
    }

    private function siswa(Siswa|Kelas|Guru $record): Siswa
    {
        return $record instanceof Siswa ? $record : throw new LogicException('Record bukan Siswa.');
    }
}
