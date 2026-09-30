<?php

namespace App\Modules\Kesiswaan\Models;

use App\Modules\Core\Models\Scopes\UnitSekolahScope;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use App\Modules\Kesiswaan\Database\Factories\SiswaFactory;
use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Enums\JenisKelamin;
use App\Modules\Kesiswaan\Enums\KebutuhanKhusus;
use App\Modules\Kesiswaan\Enums\ModaTransportasi;
use App\Modules\Kesiswaan\Enums\StatusSiswa;
use App\Modules\Kesiswaan\Enums\TempatTinggal;
use App\Modules\Kesiswaan\Policies\SiswaPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_siswa', key: 'id_siswa')]
#[Fillable([
    'id_unit_sekolah', 'status_siswa', 'nama_siswa', 'jenis_kelamin', 'nisn', 'no_seri_ijazah', 'no_seri_skhus',
    'tempat_lahir', 'tanggal_lahir', 'agama', 'kebutuhan_khusus', 'alamat_jalan', 'desa_kelurahan', 'kecamatan',
    'kabupaten_kota', 'kode_pos', 'moda_transportasi', 'tempat_tinggal', 'no_hp', 'email', 'no_kps_pkh', 'no_kip',
])]
#[ScopedBy([UnitSekolahScope::class])]
#[UsePolicy(SiswaPolicy::class)]
#[UseFactory(SiswaFactory::class)]
class Siswa extends Model
{
    /** @use HasFactory<SiswaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status_siswa' => StatusSiswa::class,
            'jenis_kelamin' => JenisKelamin::class,
            'tanggal_lahir' => 'date',
            'agama' => Agama::class,
            'kebutuhan_khusus' => KebutuhanKhusus::class,
            'moda_transportasi' => ModaTransportasi::class,
            'tempat_tinggal' => TempatTinggal::class,
        ];
    }

    /**
     * Membatasi daftar sesuai hak lihat pengguna: siswa.view = seluruh siswa di unitnya (scope unit sudah
     * berlaku), siswa.view-kelas = hanya siswa di kelas yang ia ampu (wali kelas), selain itu tidak ada.
     */
    public function scopeDapatDilihatOleh(Builder $query, User $user): void
    {
        if ($user->checkPermissionTo('siswa.view')) {
            return;
        }

        if ($user->checkPermissionTo('siswa.view-kelas')) {
            $query->whereHas('anggotaKelas', fn ($a) => $a->whereHas('kelas', fn ($k) => $k->diampuOleh($user)));

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }

    /** Ayah, ibu, dan wali (maksimal satu per hubungan) lewat pivot tb_siswa_wali. */
    public function wali(): BelongsToMany
    {
        return $this->belongsToMany(WaliSiswa::class, 'tb_siswa_wali', 'id_siswa', 'id_wali_siswa', 'id_siswa', 'id_wali_siswa')
            ->withPivot('hubungan', 'hubungan_keluarga')
            ->withTimestamps();
    }

    public function waliDengan(HubunganWali $hubungan): ?WaliSiswa
    {
        return $this->wali->first(fn (WaliSiswa $w) => $w->pivot->hubungan === $hubungan->value);
    }

    public function anggotaKelas(): HasMany
    {
        return $this->hasMany(AnggotaKelas::class, 'id_siswa', 'id_siswa');
    }
}
