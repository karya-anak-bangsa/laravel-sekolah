<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\TahunAjaran;
use App\Support\AksiDitolak;

class HapusTahunAjaran
{
    /** @throws AksiDitolak bila tahun ajaran sedang aktif atau sudah punya kelas. */
    public function __invoke(TahunAjaran $tahunAjaran): void
    {
        if ($tahunAjaran->is_aktif) {
            throw new AksiDitolak('Tahun ajaran yang sedang aktif tidak dapat dihapus. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        if (Kelas::withoutGlobalScopes()->where('id_tahun_ajaran', $tahunAjaran->getKey())->exists()) {
            throw new AksiDitolak("Tahun ajaran {$tahunAjaran->nama_tahun_ajaran} tidak dapat dihapus karena sudah memiliki kelas.");
        }

        $tahunAjaran->delete();
    }
}
