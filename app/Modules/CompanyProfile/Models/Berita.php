<?php

namespace App\Modules\CompanyProfile\Models;

use App\Modules\CompanyProfile\Database\Factories\BeritaFactory;
use App\Modules\CompanyProfile\Enums\JenisBerita;
use App\Modules\CompanyProfile\Enums\StatusBerita;
use App\Modules\CompanyProfile\Policies\BeritaPolicy;
use App\Modules\Kepegawaian\Models\Pegawai;
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
use Illuminate\Support\Str;

#[Table('tb_berita', key: 'id_berita')]
#[Fillable(['id_pegawai_penulis', 'jenis', 'judul', 'slug', 'ringkasan', 'isi', 'gambar', 'status', 'tanggal_terbit'])]
#[UsePolicy(BeritaPolicy::class)]
#[UseFactory(BeritaFactory::class)]
class Berita extends Model
{
    /** @use HasFactory<BeritaFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'jenis' => JenisBerita::class,
            'status' => StatusBerita::class,
            'tanggal_terbit' => 'datetime',
        ];
    }

    /** Yang tampil di situs publik: berstatus terbit dan tanggal terbitnya sudah tiba. */
    public function scopeTayang(Builder $query): void
    {
        $query->where('status', StatusBerita::Terbit)->where('tanggal_terbit', '<=', now());
    }

    public function scopeTerbaru(Builder $query): void
    {
        $query->orderByDesc('tanggal_terbit')->orderByDesc('id_berita');
    }

    public function urlGambar(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }

    /** Berita sudah tayang di situs publik. */
    public function sudahTayang(): bool
    {
        return $this->status === StatusBerita::Terbit && $this->tanggal_terbit !== null && $this->tanggal_terbit->lte(now());
    }

    /** @return list<string> isi dipecah per paragraf (dipisah baris kosong). */
    public function paragraf(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $this->isi))));
    }

    /** Ringkasan yang diisi penulis, atau potongan awal isi bila kosong. */
    public function ringkasanTampil(int $batas = 160): string
    {
        return $this->ringkasan ?: Str::limit(trim(preg_replace('/\s+/', ' ', (string) $this->isi)), $batas);
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai_penulis', 'id_pegawai');
    }
}
