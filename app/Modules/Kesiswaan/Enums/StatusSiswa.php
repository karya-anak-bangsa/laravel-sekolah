<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum StatusSiswa: string
{
    use Opsi;

    case Calon = 'calon';
    case Aktif = 'aktif';
    case Lulus = 'lulus';
    case Pindah = 'pindah';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::Calon => 'Calon',
            self::Aktif => 'Aktif',
            self::Lulus => 'Lulus',
            self::Pindah => 'Pindah',
            self::Keluar => 'Keluar',
        };
    }
}
