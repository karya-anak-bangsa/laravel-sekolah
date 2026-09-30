<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\Kelas;
use App\Support\AksiDitolak;

class HapusKelas
{
    /** @throws AksiDitolak bila kelas masih memiliki siswa. */
    public function __invoke(Kelas $kelas): void
    {
        if ($kelas->anggotaKelas()->exists()) {
            throw new AksiDitolak("Kelas {$kelas->nama_kelas} tidak dapat dihapus karena masih memiliki siswa. Pindahkan atau keluarkan siswanya terlebih dahulu.");
        }

        $kelas->delete();
    }
}
