<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Support\AksiDitolak;

class HapusUnitSekolah
{
    /** @throws AksiDitolak bila unit masih memiliki jurusan, kelas, atau pegawai. */
    public function __invoke(UnitSekolah $unit): void
    {
        $id = $unit->getKey();

        $dipakai = $unit->jurusan()->exists()
            || Kelas::withoutGlobalScopes()->where('id_unit_sekolah', $id)->exists()
            || Pegawai::withoutGlobalScopes()->where('id_unit_sekolah', $id)->exists();

        if ($dipakai) {
            throw new AksiDitolak("Unit \"{$unit->nama_unit_sekolah}\" tidak dapat dihapus karena masih memiliki jurusan, kelas, atau pegawai.");
        }

        $unit->delete();
    }
}
