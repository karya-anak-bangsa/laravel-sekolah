<?php

namespace Database\Seeders;

use App\Modules\Core\Database\Seeders\CoreSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeder tiap modul dipanggil dari sini seiring modulnya dikerjakan. Data dummy (pegawai, kelas, siswa)
     * hanya dibuat di environment lokal; di produksi hanya data dasar yang aman dijalankan ulang.
     */
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DataDummySeeder::class);
        }
    }
}
