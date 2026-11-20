<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataMaster\DaftarDataMasterRequest;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Perilaku bersama API Siswa/Kelas/Guru (khusus Admin):
 * - Daftar: 10 data per halaman, urutan PRD 15.6, tab Aktif/Trash lewat ?trash=1 (PRD 13.2).
 * - Detail: termasuk data di Trash.
 * - Hapus: Soft Delete (PRD 13.1); aturan relasi dijaga model (Kelas yang masih dipakai → 409).
 * - Data di Trash tidak dapat diubah/dihapus lagi; hanya data di Trash yang dapat dipulihkan (409).
 */
abstract class DataMasterController extends Controller
{
    public const PER_HALAMAN = 10;

    /**
     * Query daftar: eager loading + urutan tampilan (PRD 15.6).
     *
     * @return Builder<Siswa>|Builder<Kelas>|Builder<Guru>
     */
    abstract protected function query(): Builder;

    abstract protected function resource(Siswa|Kelas|Guru $record): JsonResource;

    /**
     * @param  LengthAwarePaginator<int, Siswa|Kelas|Guru>  $records
     */
    abstract protected function koleksi(LengthAwarePaginator $records): AnonymousResourceCollection;

    public function index(DaftarDataMasterRequest $request): AnonymousResourceCollection
    {
        $query = $this->query();

        if ($request->trash()) {
            $query->onlyTrashed();
        }

        /** @var LengthAwarePaginator<int, Siswa|Kelas|Guru> $records */
        $records = $query->paginate(self::PER_HALAMAN, page: $request->halaman());

        return $this->koleksi($records);
    }

    public function show(string $id): JsonResource
    {
        return $this->resource($this->cari($id));
    }

    public function destroy(string $id): JsonResponse
    {
        $record = $this->aktif($id);

        if ($record instanceof Kelas) {
            $alasan = $record->alasanTidakDapatDihapus();
            abort_if($alasan !== null, 409, (string) $alasan);
        }

        $record->delete();

        return response()->json(['message' => __('filament-actions::delete.single.notifications.deleted.title')]);
    }

    protected function cari(string $id): Siswa|Kelas|Guru
    {
        return $this->query()->withTrashed()->findOrFail($id);
    }

    /**
     * Record aktif (bukan di Trash) untuk diubah/dihapus.
     */
    protected function aktif(string $id): Siswa|Kelas|Guru
    {
        $record = $this->cari($id);
        abort_if($record->trashed(), 409, __('data-master.di_trash'));

        return $record;
    }

    /**
     * Record di Trash untuk dipulihkan.
     */
    protected function diTrash(string $id): Siswa|Kelas|Guru
    {
        $record = $this->cari($id);
        abort_unless($record->trashed(), 409, __('data-master.bukan_trash'));

        return $record;
    }

    protected function dibuat(Siswa|Kelas|Guru $record): JsonResponse
    {
        return $this->resource($this->muatUlang($record))
            ->additional(['message' => __('filament-actions::create.single.notifications.created.title')])
            ->response()
            ->setStatusCode(201);
    }

    protected function tersimpan(Siswa|Kelas|Guru $record): JsonResource
    {
        return $this->resource($this->muatUlang($record))
            ->additional(['message' => __('filament-actions::edit.single.notifications.saved.title')]);
    }

    protected function dipulihkan(Siswa|Kelas|Guru $record): JsonResource
    {
        return $this->resource($this->muatUlang($record))
            ->additional(['message' => __('filament-actions::restore.single.notifications.restored.title')]);
    }

    /**
     * Ambil ulang lewat query() agar relasi & hitungan yang ditampilkan selalu terbaru.
     */
    private function muatUlang(Siswa|Kelas|Guru $record): Siswa|Kelas|Guru
    {
        return $this->cari((string) $record->getKey());
    }
}
