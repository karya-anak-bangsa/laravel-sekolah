<?php

namespace App\Modules\Kesiswaan\Models;

use App\Modules\Kesiswaan\Database\Factories\WaliSiswaFactory;
use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\Pekerjaan;
use App\Modules\Kesiswaan\Enums\Pendidikan;
use App\Modules\Kesiswaan\Enums\Penghasilan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Orang tua/wali. Tidak terikat unit; diakses lewat siswa. Dapat dibagi antar kakak-adik. */
#[Table('tb_wali_siswa', key: 'id_wali_siswa')]
#[Fillable(['nama_wali_siswa', 'pendidikan', 'pekerjaan', 'penghasilan', 'no_hp', 'agama', 'alamat'])]
#[UseFactory(WaliSiswaFactory::class)]
class WaliSiswa extends Model
{
    /** @use HasFactory<WaliSiswaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'pendidikan' => Pendidikan::class,
            'pekerjaan' => Pekerjaan::class,
            'penghasilan' => Penghasilan::class,
            'agama' => Agama::class,
        ];
    }

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'tb_siswa_wali', 'id_wali_siswa', 'id_siswa', 'id_wali_siswa', 'id_siswa')
            ->withPivot('hubungan', 'hubungan_keluarga');
    }
}
