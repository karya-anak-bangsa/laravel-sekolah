<?php

namespace App\Modules\CompanyProfile\Models;

use App\Modules\CompanyProfile\Database\Factories\PrestasiFactory;
use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use App\Modules\CompanyProfile\Policies\PrestasiPolicy;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Table('tb_prestasi', key: 'id_prestasi')]
#[Fillable(['id_unit_sekolah', 'judul', 'nama_peraih', 'tingkat', 'tahun', 'gambar'])]
#[UsePolicy(PrestasiPolicy::class)]
#[UseFactory(PrestasiFactory::class)]
class Prestasi extends Model
{
    /** @use HasFactory<PrestasiFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'tingkat' => TingkatPrestasi::class,
            'tahun' => 'integer',
        ];
    }

    /** Tahun terbaru dulu; di dalam tahun, tingkat lebih tinggi dulu, lalu yang terakhir dimasukkan. */
    public function scopeTerbaru(Builder $query): void
    {
        $query->orderByDesc('tahun')
            ->orderByRaw("field(tingkat, 'nasional', 'provinsi', 'kota', 'sekolah')")
            ->orderByDesc('id_prestasi');
    }

    public function urlGambar(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }

    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'id_unit_sekolah', 'id_unit_sekolah');
    }
}
