<?php

namespace App\Http\Controllers\Api;

use App\Actions\DataMaster\SimpanGuruAction;
use App\Http\Requests\DataMaster\GuruRequest;
use App\Http\Requests\DataMaster\PulihkanGuruRequest;
use App\Http\Resources\GuruResource;
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
 * API CRUD Guru (PRD 7). Validasi: GuruRequest; penyimpanan: SimpanGuruAction (sama dengan panel Admin).
 */
final class GuruController extends DataMasterController
{
    public function store(GuruRequest $request, SimpanGuruAction $simpan): JsonResponse
    {
        return $this->dibuat($simpan->handle($request->validated()));
    }

    public function update(GuruRequest $request, string $guru, SimpanGuruAction $simpan): JsonResource
    {
        return $this->tersimpan($simpan->handle($request->validated(), $this->guru($this->aktif($guru))));
    }

    /**
     * Wajib memilih minimal satu Kelas aktif; Guru tidak pernah aktif tanpa Kelas (PRD 7.6).
     */
    public function pulihkan(PulihkanGuruRequest $request, string $guru): JsonResource
    {
        $record = $this->guru($this->diTrash($guru));
        $record->pulihkanKeKelas($request->kelasIds());

        return $this->dipulihkan($record);
    }

    /**
     * @return Builder<Guru>
     */
    protected function query(): Builder
    {
        return Guru::query()->with(['kelas', 'akun'])->orderBy('nama')->orderBy('id');
    }

    protected function resource(Siswa|Kelas|Guru $record): JsonResource
    {
        return GuruResource::make($this->guru($record));
    }

    protected function koleksi(LengthAwarePaginator $records): AnonymousResourceCollection
    {
        return GuruResource::collection($records);
    }

    private function guru(Siswa|Kelas|Guru $record): Guru
    {
        return $record instanceof Guru ? $record : throw new LogicException('Record bukan Guru.');
    }
}
