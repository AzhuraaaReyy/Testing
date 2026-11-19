<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use Database\Factories\SiswaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nis
 * @property string $nama
 * @property JenisKelamin $jenis_kelamin
 * @property string $alamat
 * @property int|null $kelas_id
 */
#[Table('siswa')]
#[Fillable(['nis', 'nama', 'jenis_kelamin', 'alamat', 'kelas_id'])]
class Siswa extends Model
{
    /** @use HasFactory<SiswaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenis_kelamin' => JenisKelamin::class,
        ];
    }

    /**
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}
