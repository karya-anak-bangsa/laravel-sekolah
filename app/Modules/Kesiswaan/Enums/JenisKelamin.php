<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum JenisKelamin: string
{
    use Opsi;

    case L = 'l';
    case P = 'p';

    public function label(): string
    {
        return match ($this) {
            self::L => 'Laki-laki',
            self::P => 'Perempuan',
        };
    }
}
