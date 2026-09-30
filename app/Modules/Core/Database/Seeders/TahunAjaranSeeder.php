<?php

namespace App\Modules\Core\Database\Seeders;

use App\Modules\Core\Enums\Semester;
use App\Modules\Core\Models\TahunAjaran;
use Illuminate\Database\Seeder;

/** Tahun ajaran awal (2026/2027, semester ganjil). Tidak mengubah tahun ajaran yang sudah ada. */
class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        TahunAjaran::firstOrCreate(
            ['nama_tahun_ajaran' => '2026/2027'],
            ['semester_aktif' => Semester::Ganjil, 'is_aktif' => ! TahunAjaran::aktif()->exists()],
        );
    }
}
