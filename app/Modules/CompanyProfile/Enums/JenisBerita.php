<?php

namespace App\Modules\CompanyProfile\Enums;

use App\Support\Opsi;

enum JenisBerita: string
{
    use Opsi;

    case Berita = 'berita';
    case Pengumuman = 'pengumuman';

    public function label(): string
    {
        return match ($this) {
            self::Berita => 'Berita',
            self::Pengumuman => 'Pengumuman',
        };
    }
}
