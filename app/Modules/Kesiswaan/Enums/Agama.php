<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum Agama: string
{
    use Opsi;

    case Islam = 'islam';
    case Katolik = 'katolik';
    case Kristen = 'kristen';
    case Hindu = 'hindu';
    case Budha = 'budha';
    case Konghucu = 'konghucu';

    public function label(): string
    {
        return match ($this) {
            self::Islam => 'Islam',
            self::Katolik => 'Katolik',
            self::Kristen => 'Kristen',
            self::Hindu => 'Hindu',
            self::Budha => 'Buddha',
            self::Konghucu => 'Konghucu',
        };
    }
}
