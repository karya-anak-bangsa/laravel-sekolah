<?php

namespace App\Modules\CompanyProfile\Database\Factories;

use App\Modules\CompanyProfile\Models\Galeri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Data dummy galeri. Berkas gambarnya tidak dibuat; path hanya penanda.
 *
 * @extends Factory<Galeri>
 */
class GaleriFactory extends Factory
{
    protected $model = Galeri::class;

    public function definition(): array
    {
        $nama = fake()->unique()->numerify('foto-########');

        return [
            'judul' => rtrim(fake()->sentence(random_int(2, 5)), '.'),
            'gambar' => "galeri/{$nama}.webp",
            'gambar_kecil' => "galeri/kecil-{$nama}.webp",
        ];
    }
}
