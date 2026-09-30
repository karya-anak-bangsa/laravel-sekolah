<?php

namespace App\Modules\Core\Database\Factories;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /** Default: akun pegawai (login dengan username). */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'no_hp' => null,
            'email' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /** Akun pendaftar PPDB (login dengan no HP). */
    public function pendaftar(): static
    {
        return $this->state(fn () => [
            'username' => null,
            'no_hp' => '08'.fake()->unique()->numerify('##########'),
        ]);
    }
}
