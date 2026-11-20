<?php

namespace App\Http\Controllers\Api;

use App\Actions\DataMaster\SimpanKelasAction;
use App\Http\Requests\DataMaster\KelasRequest;
use App\Http\Resources\KelasResource;
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
 * API CRUD Kelas (PRD 6). Validasi: KelasRequest; penyimpanan: SimpanKelasAction (sama dengan panel Admin).
 */
final class KelasController extends DataMasterController
{
    public function store(KelasRequest $request, SimpanKelasAction $simpan): JsonResponse
    {
        return $this->dibuat($simpan->handle($request->validated()));
    }

    public function update(KelasRequest $request, string $kelas, SimpanKelasAction $simpan): JsonResource
    {
        return $this->tersimpan($simpan->handle($request->validated(), $this->kelas($this->aktif($kelas))));
    }

    /**
     * Pemulihan Kelas tidak mengembalikan relasi lama secara otomatis (PRD 6.6).
     */
    public function pulihkan(string $kelas): JsonResource
    {
        $record = $this->kelas($this->diTrash($kelas));
        $record->restore();

        return $this->dipulihkan($record);
    }

    /**
     * @return Builder<Kelas>
     */
    protected function query(): Builder
    {
        return Kelas::query()->withCount(['siswa', 'guru'])->urutanTampilan()->orderBy('kelas.id');
    }

    protected function resource(Siswa|Kelas|Guru $record): JsonResource
    {
        return KelasResource::make($this->kelas($record));
    }

    protected function koleksi(LengthAwarePaginator $records): AnonymousResourceCollection
    {
        return KelasResource::collection($records);
    }

    private function kelas(Siswa|Kelas|Guru $record): Kelas
    {
        return $record instanceof Kelas ? $record : throw new LogicException('Record bukan Kelas.');
    }
}
