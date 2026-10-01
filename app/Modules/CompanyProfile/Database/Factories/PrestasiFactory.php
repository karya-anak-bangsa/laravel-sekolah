<?php

namespace App\Modules\CompanyProfile\Database\Factories;

use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use App\Modules\CompanyProfile\Models\Prestasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Data dummy prestasi (Faker id_ID). Bawaan tingkat yayasan; beri id_unit_sekolah untuk prestasi unit.
 *
 * @extends Factory<Prestasi>
 */
class PrestasiFactory extends Factory
{
    protected $model = Prestasi::class;

    public function definition(): array
    {
        return [
            'judul' => 'Juara '.fake()->numberBetween(1, 3).' '.fake()->randomElement(['Lomba Cerdas Cermat', 'Olimpiade Sains', 'Lomba Web Design', 'Festival Seni', 'Kejuaraan Futsal']),
            'nama_peraih' => fake()->name(),
            'tingkat' => fake()->randomElement(TingkatPrestasi::cases()),
            'tahun' => fake()->numberBetween(2022, 2026),
        ];
    }
}
