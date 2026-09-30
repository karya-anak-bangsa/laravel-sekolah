<?php

namespace App\Modules\Kepegawaian\Enums;

enum JenisPegawai: string
{
    case Guru = 'guru';
    case Tu = 'tu';
    case PimpinanYayasan = 'pimpinan_yayasan';

    public function label(): string
    {
        return match ($this) {
            self::Guru => 'Guru',
            self::Tu => 'Tata Usaha',
            self::PimpinanYayasan => 'Pimpinan Yayasan',
        };
    }
}
