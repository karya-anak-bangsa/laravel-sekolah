<?php

namespace App\Modules\Core\Enums;

enum Jenjang: string
{
    case Smp = 'smp';
    case Smk = 'smk';

    public function label(): string
    {
        return match ($this) {
            self::Smp => 'SMP',
            self::Smk => 'SMK',
        };
    }
}
