<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\Jenjang;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_unit_sekolah', key: 'id_unit_sekolah')]
#[Fillable(['nama_unit_sekolah', 'jenjang'])]
class UnitSekolah extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenjang' => Jenjang::class,
        ];
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
