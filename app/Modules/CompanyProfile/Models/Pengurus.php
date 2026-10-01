<?php

namespace App\Modules\CompanyProfile\Models;

use App\Modules\CompanyProfile\Policies\PengurusPolicy;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_pengurus', key: 'id_pengurus')]
#[Fillable(['id_unit_sekolah', 'nama_pengurus', 'jabatan', 'urutan'])]
#[UsePolicy(PengurusPolicy::class)]
class Pengurus extends Model
{
    use SoftDeletes;

    /** Tingkat yayasan lebih dulu, lalu per unit; di dalam kelompok menurut urutan. */
    public function scopeTerurut(Builder $query): void
    {
        $query->orderByRaw('id_unit_sekolah is not null')->orderBy('id_unit_sekolah')->orderBy('urutan')->orderBy('id_pengurus');
    }

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }
}
