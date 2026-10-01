<?php

namespace App\Modules\CompanyProfile\Enums;

use App\Support\Opsi;

enum StatusBerita: string
{
    use Opsi;

    case Draf = 'draf';
    case Terbit = 'terbit';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Terbit => 'Terbit',
        };
    }
}
