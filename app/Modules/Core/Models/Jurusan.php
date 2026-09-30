<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Scopes\UnitSekolahScope;
use App\Modules\Core\Policies\JurusanPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_jurusan', key: 'id_jurusan')]
#[Fillable(['id_unit_sekolah', 'nama_jurusan', 'kode_jurusan'])]
#[ScopedBy([UnitSekolahScope::class])]
#[UsePolicy(JurusanPolicy::class)]
class Jurusan extends Model
{
    use SoftDeletes;

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'id_jurusan', 'id_jurusan');
    }
}
