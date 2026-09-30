<?php

namespace App\Modules\Kepegawaian\Database\Factories;

use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Pegawai> */
class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;

    public function definition(): array
    {
        return [
            'nama_pegawai' => fake()->name(),
            'jenis_pegawai' => JenisPegawai::Guru,
            'id_unit_sekolah' => null,
        ];
    }

    public function guru(): static
    {
        return $this->state(['jenis_pegawai' => JenisPegawai::Guru]);
    }

    public function tu(): static
    {
        return $this->state(['jenis_pegawai' => JenisPegawai::Tu]);
    }

    public function pimpinanYayasan(): static
    {
        return $this->state(['jenis_pegawai' => JenisPegawai::PimpinanYayasan, 'id_unit_sekolah' => null]);
    }

    public function diUnit(int $idUnit): static
    {
        return $this->state(['id_unit_sekolah' => $idUnit]);
    }
}
