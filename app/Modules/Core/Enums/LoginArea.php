<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Models\User;
use App\Support\NomorHp;
use Illuminate\Support\Str;

/** Dua pintu masuk dengan kredensial berbeda pada satu tabel tb_user dan satu guard web. */
enum LoginArea: string
{
    case Admin = 'admin';
    case Ppdb = 'ppdb';

    /** Kolom identitas yang dicek di halaman login ini. */
    public function field(): string
    {
        return match ($this) {
            self::Admin => 'username',
            self::Ppdb => 'no_hp',
        };
    }

    public function normalisasi(string $identitas): string
    {
        return match ($this) {
            self::Admin => Str::lower(trim($identitas)),
            self::Ppdb => NomorHp::normalisasi($identitas),
        };
    }

    /** Login pegawai menolak akun pendaftar, dan sebaliknya. */
    public function mengizinkan(User $user): bool
    {
        $pendaftar = $user->hasRole(Role::Pendaftar->value);

        return match ($this) {
            self::Admin => ! $pendaftar,
            self::Ppdb => $pendaftar,
        };
    }

    /** Satu pesan untuk semua penyebab gagal agar tidak membocorkan keberadaan akun. */
    public function pesanGagal(): string
    {
        return match ($this) {
            self::Admin => 'Username atau password salah.',
            self::Ppdb => 'Nomor HP atau kata sandi salah.',
        };
    }
}
