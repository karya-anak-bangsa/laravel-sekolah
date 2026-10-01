<?php

namespace App\Modules\CompanyProfile\Models;

use App\Modules\CompanyProfile\Database\Factories\GaleriFactory;
use App\Modules\CompanyProfile\Policies\GaleriPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Table('tb_galeri', key: 'id_galeri')]
#[Fillable(['judul', 'gambar', 'gambar_kecil'])]
#[UsePolicy(GaleriPolicy::class)]
#[UseFactory(GaleriFactory::class)]
class Galeri extends Model
{
    /** @use HasFactory<GaleriFactory> */
    use HasFactory, SoftDeletes;

    public function scopeTerbaru(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id_galeri');
    }

    public function urlGambar(): string
    {
        return Storage::disk('public')->url($this->gambar);
    }

    public function urlGambarKecil(): string
    {
        return Storage::disk('public')->url($this->gambar_kecil);
    }
}
