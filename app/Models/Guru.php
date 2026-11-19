<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use Database\Factories\GuruFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $nip
 * @property string $nama
 * @property JenisKelamin $jenis_kelamin
 * @property string $alamat
 * @property string $no_telepon
 */
#[Table('guru')]
#[Fillable(['nip', 'nama', 'jenis_kelamin', 'alamat', 'no_telepon'])]
class Guru extends Model
{
    /** @use HasFactory<GuruFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenis_kelamin' => JenisKelamin::class,
        ];
    }

    /**
     * @return BelongsToMany<Kelas, $this>
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')->withTimestamps();
    }
}
