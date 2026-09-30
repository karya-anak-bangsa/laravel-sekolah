<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum Pendidikan: string
{
    use Opsi;

    case TidakSekolah = 'tidak_sekolah';
    case Sd = 'sd';
    case Smp = 'smp';
    case Sma = 'sma';
    case D1 = 'd1';
    case D2 = 'd2';
    case D3 = 'd3';
    case S1 = 's1';
    case S2 = 's2';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::TidakSekolah => 'Tidak sekolah',
            self::Sd => 'SD/sederajat',
            self::Smp => 'SMP/sederajat',
            self::Sma => 'SMA/sederajat',
            self::D1 => 'D1',
            self::D2 => 'D2',
            self::D3 => 'D3',
            self::S1 => 'S1',
            self::S2 => 'S2',
            self::S3 => 'S3',
        };
    }
}
