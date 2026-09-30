<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Policies\UnitSekolahPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_unit_sekolah', key: 'id_unit_sekolah')]
#[Fillable(['nama_unit_sekolah', 'jenjang'])]
#[UsePolicy(UnitSekolahPolicy::class)]
class UnitSekolah extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenjang' => Jenjang::class,
        ];
    }

    /** Unit yang boleh dilihat/dipilih pengguna: hanya unitnya sendiri bila pengguna tingkat unit. */
    public function scopeDapatDiaksesOleh(Builder $query, ?User $user): void
    {
        $idUnit = $user?->idUnitSekolah();

        if ($idUnit !== null) {
            $query->whereKey($idUnit);
        }
    }

    public function jurusan(): HasMany
    {
        return $this->hasMany(Jurusan::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }
}
