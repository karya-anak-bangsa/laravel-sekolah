<?php

namespace App\Modules\Core\Database\Seeders;

use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use Illuminate\Database\Seeder;

/**
 * Membuat akun Super Administrator awal dari ADMIN_USERNAME / ADMIN_PASSWORD (.env).
 * Akun yang sudah ada tidak diubah (kata sandi tidak ditimpa); hanya dipastikan memiliki role-nya.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = config('sekolah.admin_awal.username');
        $password = config('sekolah.admin_awal.password');

        if (blank($username) || blank($password)) {
            $this->command?->warn('ADMIN_USERNAME / ADMIN_PASSWORD belum diisi di .env; akun super_admin tidak dibuat.');

            return;
        }

        $user = User::withTrashed()->where('username', $username)->first()
            ?? User::create([
                'nama' => config('sekolah.admin_awal.nama'),
                'username' => $username,
                'password' => $password,
            ]);

        $user->assignRole(Role::SuperAdmin->value);
    }
}
