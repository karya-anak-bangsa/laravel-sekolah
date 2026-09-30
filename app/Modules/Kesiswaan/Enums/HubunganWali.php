<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum HubunganWali: string
{
    use Opsi;

    case Ayah = 'ayah';
    case Ibu = 'ibu';
    case Wali = 'wali';

    public function label(): string
    {
        return match ($this) {
            self::Ayah => 'Ayah',
            self::Ibu => 'Ibu',
            self::Wali => 'Wali',
        };
    }
}
