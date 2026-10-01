<?php

namespace App\Modules\CompanyProfile\Enums;

use App\Support\Opsi;

enum TingkatPrestasi: string
{
    use Opsi;

    case Sekolah = 'sekolah';
    case Kota = 'kota';
    case Provinsi = 'provinsi';
    case Nasional = 'nasional';

    public function label(): string
    {
        return match ($this) {
            self::Sekolah => 'Sekolah',
            self::Kota => 'Kota/Kabupaten',
            self::Provinsi => 'Provinsi',
            self::Nasional => 'Nasional',
        };
    }
}
