<?php

namespace App\Modules\Kepegawaian\Actions;

use App\Modules\Kepegawaian\Models\Pegawai;
use App\Support\AksiDitolak;
use Illuminate\Support\Facades\DB;

class HapusPegawai
{
    /**
     * Menghapus (soft delete) pegawai beserta akun loginnya agar tidak bisa masuk lagi.
     *
     * @throws AksiDitolak bila pegawai masih menjadi wali kelas.
     */
    public function __invoke(Pegawai $pegawai): void
    {
        if ($pegawai->kelasDiampu()->exists()) {
            throw new AksiDitolak("{$pegawai->nama_pegawai} masih menjadi wali kelas. Ganti wali kelasnya terlebih dahulu.");
        }

        DB::transaction(function () use ($pegawai) {
            $pegawai->user()->delete();
            $pegawai->delete();
        });
    }
}
