<?php

namespace App\Modules\Kepegawaian\Models;

use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\Scopes\UnitSekolahScope;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Database\Factories\PegawaiFactory;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Policies\PegawaiPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_pegawai', key: 'id_pegawai')]
#[Fillable(['id_unit_sekolah', 'nama_pegawai', 'jenis_pegawai'])]
#[ScopedBy([UnitSekolahScope::class])]
#[UsePolicy(PegawaiPolicy::class)]
#[UseFactory(PegawaiFactory::class)]
class Pegawai extends Model
{
    /** @use HasFactory<PegawaiFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenis_pegawai' => JenisPegawai::class,
        ];
    }

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id_pegawai', 'id_pegawai');
    }

    public function kelasDiampu(): HasMany
    {
        return $this->hasMany(Kelas::class, 'id_pegawai_wali_kelas', 'id_pegawai');
    }
}
