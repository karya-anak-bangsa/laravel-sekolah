<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Enums\Semester;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('tb_tahun_ajaran', key: 'id_tahun_ajaran')]
#[Fillable(['nama_tahun_ajaran', 'semester_aktif', 'is_aktif'])]
class TahunAjaran extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'semester_aktif' => Semester::class,
            'is_aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('is_aktif', true);
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }
}
