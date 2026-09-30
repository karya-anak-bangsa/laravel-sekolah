<?php

namespace App\Modules\Kesiswaan\Enums;

use App\Support\Opsi;

enum Pekerjaan: string
{
    use Opsi;

    case TidakBekerja = 'tidak_bekerja';
    case Nelayan = 'nelayan';
    case Petani = 'petani';
    case TniPolri = 'tni_polri';
    case KaryawanSwasta = 'karyawan_swasta';
    case Pns = 'pns';
    case PedagangKecil = 'pedagang_kecil';
    case PedagangBesar = 'pedagang_besar';
    case Wiraswasta = 'wiraswasta';
    case Buruh = 'buruh';
    case Pensiunan = 'pensiunan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::TidakBekerja => 'Tidak bekerja',
            self::Nelayan => 'Nelayan',
            self::Petani => 'Petani',
            self::TniPolri => 'TNI/Polri',
            self::KaryawanSwasta => 'Karyawan swasta',
            self::Pns => 'PNS',
            self::PedagangKecil => 'Pedagang kecil',
            self::PedagangBesar => 'Pedagang besar',
            self::Wiraswasta => 'Wiraswasta',
            self::Buruh => 'Buruh',
            self::Pensiunan => 'Pensiunan',
            self::Lainnya => 'Lainnya',
        };
    }
}
