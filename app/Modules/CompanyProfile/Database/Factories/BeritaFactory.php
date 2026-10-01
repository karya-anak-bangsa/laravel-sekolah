<?php

namespace App\Modules\CompanyProfile\Database\Factories;

use App\Modules\CompanyProfile\Enums\JenisBerita;
use App\Modules\CompanyProfile\Enums\StatusBerita;
use App\Modules\CompanyProfile\Models\Berita;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Data dummy berita/pengumuman. Bawaan: sudah terbit kemarin. Gunakan ->draf() atau ->terjadwal() untuk keadaan lain.
 *
 * @extends Factory<Berita>
 */
class BeritaFactory extends Factory
{
    protected $model = Berita::class;

    public function definition(): array
    {
        $judul = rtrim(fake()->sentence(random_int(4, 8)), '.');

        return [
            'jenis' => JenisBerita::Berita,
            'judul' => $judul,
            'slug' => Str::slug($judul).'-'.fake()->unique()->numerify('####'),
            'ringkasan' => fake()->sentence(14),
            'isi' => implode("\n\n", fake()->paragraphs(3)),
            'status' => StatusBerita::Terbit,
            'tanggal_terbit' => now()->subDay(),
        ];
    }

    public function pengumuman(): static
    {
        return $this->state(['jenis' => JenisBerita::Pengumuman]);
    }

    public function draf(): static
    {
        return $this->state(['status' => StatusBerita::Draf, 'tanggal_terbit' => null]);
    }

    public function terjadwal(): static
    {
        return $this->state(['status' => StatusBerita::Terbit, 'tanggal_terbit' => now()->addDays(3)]);
    }
}
