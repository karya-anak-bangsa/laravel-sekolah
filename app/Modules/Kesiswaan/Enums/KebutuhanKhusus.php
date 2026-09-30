<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum KebutuhanKhusus: string
{
    use Opsi;

    case TidakAda = 'tidak_ada';
    case TunaNetra = 'tuna_netra';
    case TunaRungu = 'tuna_rungu';
    case GrahitaRingan = 'grahita_ringan';
    case GrahitaSedang = 'grahita_sedang';
    case Indigo = 'indigo';
    case DownSindrom = 'down_sindrom';
    case Autis = 'autis';
    case TunaWicara = 'tuna_wicara';
    case TunaGanda = 'tuna_ganda';
    case HiperAktif = 'hiper_aktif';
    case KesulitanBelajar = 'kesulitan_belajar';
    case Narkoba = 'narkoba';

    public function label(): string
    {
        return match ($this) {
            self::TidakAda => 'Tidak ada',
            self::TunaNetra => 'Tuna netra',
            self::TunaRungu => 'Tuna rungu',
            self::GrahitaRingan => 'Tuna grahita ringan',
            self::GrahitaSedang => 'Tuna grahita sedang',
            self::Indigo => 'Indigo',
            self::DownSindrom => 'Down sindrom',
            self::Autis => 'Autis',
            self::TunaWicara => 'Tuna wicara',
            self::TunaGanda => 'Tuna ganda',
            self::HiperAktif => 'Hiper aktif',
            self::KesulitanBelajar => 'Kesulitan belajar',
            self::Narkoba => 'Narkoba',
        };
    }
}
