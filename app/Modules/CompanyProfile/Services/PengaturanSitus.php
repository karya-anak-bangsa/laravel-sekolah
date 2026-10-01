<?php

namespace App\Modules\CompanyProfile\Services;

use App\Modules\CompanyProfile\Models\Pengaturan;
use App\Modules\CompanyProfile\Support\KatalogPengaturan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Membaca dan menyimpan pengaturan situs. Nilai dari tb_pengaturan disimpan di cache dan dibersihkan
 * saat disimpan; kunci yang belum tersimpan memakai nilai bawaan dari KatalogPengaturan.
 */
class PengaturanSitus
{
    public const CACHE_KEY = 'pengaturan_situs';

    /** @var array<string, ?string>|null salinan per request agar tidak membaca cache berulang. */
    private ?array $nilai = null;

    public function get(string $kunci, ?string $bawaan = null): ?string
    {
        $nilai = $this->semua()[$kunci] ?? null;

        return $nilai === null || $nilai === '' ? $bawaan : $nilai;
    }

    /** @return array<string, ?string> seluruh kunci katalog, nilai tersimpan atau bawaan. */
    public function semua(): array
    {
        return $this->nilai ??= $this->muat();
    }

    /**
     * Menyimpan nilai untuk kunci yang dikenal saja; nilai kosong disimpan sebagai null.
     *
     * @param  array<string, ?string>  $data
     */
    public function simpan(array $data): void
    {
        $data = array_intersect_key($data, array_flip(KatalogPengaturan::kunci()));

        DB::transaction(function () use ($data) {
            foreach ($data as $kunci => $nilai) {
                $nilai = is_string($nilai) ? trim($nilai) : $nilai;

                Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai === '' ? null : $nilai]);
            }
        });

        Cache::forget(self::CACHE_KEY);
        $this->nilai = null;
    }

    /** @return array<string, ?string> */
    private function muat(): array
    {
        try {
            $tersimpan = Cache::rememberForever(self::CACHE_KEY, fn () => Pengaturan::query()->pluck('nilai', 'kunci')->all());
        } catch (QueryException) {
            $tersimpan = []; // tabel belum ada/DB bermasalah; halaman error pun harus tetap tampil, dan hasil ini tidak di-cache
        }

        $hasil = [];

        foreach (KatalogPengaturan::semua() as $kunci => $definisi) {
            // Kunci yang sengaja dikosongkan (null) tetap kosong, bukan kembali ke bawaan.
            $hasil[$kunci] = array_key_exists($kunci, $tersimpan) ? $tersimpan[$kunci] : $definisi['bawaan'];
        }

        return $hasil;
    }
}
