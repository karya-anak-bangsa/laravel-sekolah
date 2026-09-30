<?php

namespace App\Modules\Kesiswaan\Models;

use App\Modules\Core\Models\Kelas;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_anggota_kelas', key: 'id_anggota_kelas')]
#[Fillable(['id_siswa', 'id_kelas'])]
class AnggotaKelas extends Model
{
    use SoftDeletes;

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
