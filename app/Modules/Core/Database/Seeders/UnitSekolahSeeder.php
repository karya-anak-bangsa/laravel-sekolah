<?php

namespace App\Modules\Core\Database\Seeders;

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Database\Seeder;

/**
 * Struktur tetap yayasan: unit SMP dan SMK, serta 4 jurusan SMK. Idempotent dan aman di produksi.
 * Nama unit dan kode jurusan adalah asumsi (lihat docs/asumsi.md) sampai ada data resmi dari yayasan.
 */
class UnitSekolahSeeder extends Seeder
{
    public function run(): void
    {
        UnitSekolah::firstOrCreate(
            ['jenjang' => Jenjang::Smp->value],
            ['nama_unit_sekolah' => 'SMP Puspita Bangsa'],
        );

        $smk = UnitSekolah::firstOrCreate(
            ['jenjang' => Jenjang::Smk->value],
            ['nama_unit_sekolah' => 'SMK Puspita Bangsa'],
        );

        $jurusan = [
            'PAR' => 'Pariwisata',
            'BM' => 'Bisnis Manajemen',
            'TKJ' => 'Teknik Komputer dan Jaringan',
            'RPL' => 'Rekayasa Perangkat Lunak',
        ];

        foreach ($jurusan as $kode => $nama) {
            $smk->jurusan()->firstOrCreate(['kode_jurusan' => $kode], ['nama_jurusan' => $nama]);
        }
    }
}
