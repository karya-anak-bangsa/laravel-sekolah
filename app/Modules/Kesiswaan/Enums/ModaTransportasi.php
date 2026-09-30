<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum ModaTransportasi: string
{
    use Opsi;

    case JalanKaki = 'jalan_kaki';
    case KendaraanPribadi = 'kendaraan_pribadi';
    case KendaraanUmum = 'kendaraan_umum';
    case JemputanSekolah = 'jemputan_sekolah';

    public function label(): string
    {
        return match ($this) {
            self::JalanKaki => 'Jalan kaki',
            self::KendaraanPribadi => 'Kendaraan pribadi',
            self::KendaraanUmum => 'Kendaraan umum',
            self::JemputanSekolah => 'Jemputan sekolah',
        };
    }
}
