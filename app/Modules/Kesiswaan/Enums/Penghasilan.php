<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum Penghasilan: string
{
    use Opsi;

    case Kurang500rb = 'kurang_500rb';
    case Antara500rb1jt = '500rb_1jt';
    case Antara1jt2jt = '1jt_2jt';
    case Antara2jt5jt = '2jt_5jt';
    case Antara5jt20jt = '5jt_20jt';
    case Lebih20jt = 'lebih_20jt';

    public function label(): string
    {
        return match ($this) {
            self::Kurang500rb => 'Kurang dari Rp500.000',
            self::Antara500rb1jt => 'Rp500.000 â€“ Rp1.000.000',
            self::Antara1jt2jt => 'Rp1.000.000 â€“ Rp2.000.000',
            self::Antara2jt5jt => 'Rp2.000.000 â€“ Rp5.000.000',
            self::Antara5jt20jt => 'Rp5.000.000 â€“ Rp20.000.000',
            self::Lebih20jt => 'Lebih dari Rp20.000.000',
        };
    }
}
