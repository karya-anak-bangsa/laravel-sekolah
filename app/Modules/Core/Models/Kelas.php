<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Scopes\UnitSekolahScope;
use App\Modules\Core\Policies\KelasPolicy;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_kelas', key: 'id_kelas')]
#[Fillable(['id_unit_sekolah', 'id_jurusan', 'id_tahun_ajaran', 'tingkat', 'nama_kelas', 'id_pegawai_wali_kelas'])]
#[ScopedBy([UnitSekolahScope::class])]
#[UsePolicy(KelasPolicy::class)]
class Kelas extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'tingkat' => 'integer',
        ];
    }

    /** Kelas tahun ajaran aktif yang wali kelasnya adalah pegawai pemilik akun ini. */
    public function scopeDiampuOleh(Builder $query, User $user): void
    {
        $query->where('id_pegawai_wali_kelas', $user->id_pegawai)
            ->whereHas('tahunAjaran', fn ($t) => $t->aktif());
    }

    public function scopeDiTahunAktif(Builder $query): void
    {
        $query->whereHas('tahunAjaran', fn ($t) => $t->aktif());
    }

    public function anggotaKelas(): HasMany
    {
        return $this->hasMany(AnggotaKelas::class, 'id_kelas', 'id_kelas');
    }

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id_jurusan');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai_wali_kelas', 'id_pegawai');
    }
}
