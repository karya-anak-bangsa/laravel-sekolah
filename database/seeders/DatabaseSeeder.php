<?php

namespace Database\Seeders;

use App\Modules\Core\Database\Seeders\CoreSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeder tiap modul dipanggil dari sini seiring modulnya dikerjakan.
     */
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
        ]);
    }
}
