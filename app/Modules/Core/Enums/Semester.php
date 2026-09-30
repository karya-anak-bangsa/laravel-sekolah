<?php

namespace App\Modules\Core\Enums;

enum Semester: string
{
    case Ganjil = 'ganjil';
    case Genap = 'genap';

    public function label(): string
    {
        return match ($this) {
            self::Ganjil => 'Ganjil',
            self::Genap => 'Genap',
        };
    }
}
