<?php

namespace App\Modules\CompanyProfile\Support;

use App\Modules\CompanyProfile\Models\Berita;
use App\Modules\CompanyProfile\Models\Galeri;
use App\Modules\CompanyProfile\Models\Prestasi;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Cache konten beranda (berita, galeri, prestasi terbaru). TTL pendek karena berita terjadwal tayang tanpa
 * ada penyimpanan; dibersihkan juga setiap kali kontennya disimpan atau dihapus lewat panel admin.
 *
 * Yang disimpan di cache hanya atribut mentah (array), bukan objek model: Laravel tidak meng-unserialize
 * kelas PHP dari cache (`serializable_classes` = false), dan model dibentuk ulang dengan hydrate().
 */
final class CacheBeranda
{
    private const KUNCI = 'beranda';

    private const TTL_MENIT = 10;

    /** Satu berita utama dan tiga berita samping di beranda. */
    public const JUMLAH_BERITA = 4;

    public const JUMLAH_GALERI = 6;

    public const JUMLAH_PRESTASI = 3;

    /** @return Collection<int, Berita> isi hanya 400 karakter pertama (cukup untuk kutipan). */
    public static function berita(): Collection
    {
        return self::ingat('berita', Berita::class, fn () => Berita::query()
            ->tayang()
            ->terbaru()
            ->limit(self::JUMLAH_BERITA)
            ->selectRaw('id_berita, jenis, judul, slug, ringkasan, gambar, tanggal_terbit, left(isi, 400) as isi')
            ->get());
    }

    /** @return Collection<int, Galeri> */
    public static function galeri(): Collection
    {
        return self::ingat('galeri', Galeri::class, fn () => Galeri::query()->terbaru()->limit(self::JUMLAH_GALERI)->get());
    }

    /** @return Collection<int, Prestasi> */
    public static function prestasi(): Collection
    {
        return self::ingat('prestasi', Prestasi::class, fn () => Prestasi::query()->terbaru()->limit(self::JUMLAH_PRESTASI)->get());
    }

    /** Membersihkan semua bagian, atau hanya yang disebut ('berita', 'galeri', 'prestasi'). */
    public static function lupakan(string ...$bagian): void
    {
        foreach ($bagian ?: ['berita', 'galeri', 'prestasi'] as $nama) {
            Cache::forget(self::KUNCI.':'.$nama);
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  Closure(): Collection<int, TModel>  $ambil
     * @return Collection<int, TModel>
     */
    private static function ingat(string $bagian, string $model, Closure $ambil): Collection
    {
        $baris = Cache::remember(
            self::KUNCI.':'.$bagian,
            now()->addMinutes(self::TTL_MENIT),
            fn () => $ambil()->map(fn (Model $item) => $item->getAttributes())->all(),
        );

        return $model::hydrate($baris);
    }
}
