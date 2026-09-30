<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\Kelas;
use App\Support\AksiDitolak;

class HapusJurusan
{
    /** @throws AksiDitolak bila jurusan masih dipakai kelas. */
    public function __invoke(Jurusan $jurusan): void
    {
        if (Kelas::withoutGlobalScopes()->where('id_jurusan', $jurusan->getKey())->exists()) {
            throw new AksiDitolak("Jurusan \"{$jurusan->nama_jurusan}\" tidak dapat dihapus karena masih dipakai kelas.");
        }

        $jurusan->delete();
    }
}
