<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\User;
use App\Support\PasswordAcak;

class ResetPasswordPengguna
{
    /** Mengganti kata sandi dengan yang sementara dan mewajibkan penggantian saat login berikutnya. */
    public function __invoke(User $pengguna): string
    {
        $password = PasswordAcak::buat();

        $pengguna->update(['password' => $password, 'wajib_ganti_password' => true]);

        return $password;
    }
}
