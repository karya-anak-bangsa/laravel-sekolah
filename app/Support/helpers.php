<?php

use App\Modules\CompanyProfile\Services\PengaturanSitus;

if (! function_exists('situs')) {
    /**
     * Pengaturan situs (identitas, kontak, profil) dari tb_pengaturan dengan nilai bawaan katalog.
     * Dipakai layout dan view lintas modul, mis. situs('nama_singkat').
     */
    function situs(string $kunci, ?string $bawaan = null): ?string
    {
        return app(PengaturanSitus::class)->get($kunci, $bawaan);
    }
}
