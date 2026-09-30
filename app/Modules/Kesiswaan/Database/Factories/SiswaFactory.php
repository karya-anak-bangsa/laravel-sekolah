<?php

namespace App\Modules\Kesiswaan\Database\Factories;

use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\JenisKelamin;
use App\Modules\Kesiswaan\Enums\ModaTransportasi;
use App\Modules\Kesiswaan\Enums\StatusSiswa;
use App\Modules\Kesiswaan\Enums\TempatTinggal;
use App\Modules\Kesiswaan\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Data dummy siswa (Faker id_ID). id_unit_sekolah harus diberikan: Siswa::factory()->create(['id_unit_sekolah' => ...]).
 *
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        $jk = fake()->randomElement(JenisKelamin::cases());

        return [
            'status_siswa' => StatusSiswa::Aktif,
            'nama_siswa' => fake()->name($jk === JenisKelamin::L ? 'male' : 'female'),
            'jenis_kelamin' => $jk,
            'nisn' => fake()->unique()->numerify('00########'),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
            'agama' => fake()->randomElement([Agama::Islam, Agama::Islam, Agama::Islam, Agama::Kristen, Agama::Katolik, Agama::Hindu]),
            'alamat_jalan' => fake()->streetAddress(),
            'desa_kelurahan' => fake()->city(),
            'kecamatan' => fake()->city(),
            'kabupaten_kota' => fake()->city(),
            'kode_pos' => fake()->numerify('#####'),
            'moda_transportasi' => fake()->randomElement(ModaTransportasi::cases()),
            'tempat_tinggal' => fake()->randomElement([TempatTinggal::BersamaOrangTua, TempatTinggal::BersamaOrangTua, TempatTinggal::Kos]),
            'no_hp' => fake()->numerify('08##########'),
        ];
    }

    public function calon(): static
    {
        return $this->state(['status_siswa' => StatusSiswa::Calon]);
    }
}
