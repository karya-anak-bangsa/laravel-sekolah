<?php

namespace App\Modules\Kesiswaan\Actions;

use App\Modules\Core\Models\Kelas;
use App\Modules\Kesiswaan\Enums\StatusSiswa;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use App\Modules\Kesiswaan\Models\Siswa;
use App\Support\AksiDitolak;
use Illuminate\Support\Facades\DB;

class TempatkanSiswaDiKelas
{
    /**
     * Menempatkan siswa ke kelas. Satu siswa hanya boleh di satu kelas per tahun ajaran, jadi bila ia sudah
     * punya kelas pada tahun ajaran yang sama, penempatannya dipindahkan.
     *
     * @throws AksiDitolak bila unit berbeda atau status siswa tidak memungkinkan.
     */
    public function __invoke(Siswa $siswa, Kelas $kelas): AnggotaKelas
    {
        if ($siswa->id_unit_sekolah !== $kelas->id_unit_sekolah) {
            throw new AksiDitolak('Siswa hanya dapat ditempatkan di kelas pada unit sekolah yang sama.');
        }

        if (! in_array($siswa->status_siswa, [StatusSiswa::Calon, StatusSiswa::Aktif], true)) {
            throw new AksiDitolak("Siswa berstatus {$siswa->status_siswa->label()} tidak dapat ditempatkan di kelas.");
        }

        return DB::transaction(function () use ($siswa, $kelas) {
            // Kunci baris siswa agar dua permintaan bersamaan tidak membuat dua penempatan.
            Siswa::withoutGlobalScopes()->whereKey($siswa->getKey())->lockForUpdate()->first();

            $sebelumnya = AnggotaKelas::query()
                ->where('id_siswa', $siswa->getKey())
                ->whereHas('kelas', fn ($k) => $k->where('id_tahun_ajaran', $kelas->id_tahun_ajaran))
                ->first();

            if ($sebelumnya) {
                $sebelumnya->update(['id_kelas' => $kelas->getKey()]);

                return $sebelumnya;
            }

            return AnggotaKelas::create(['id_siswa' => $siswa->getKey(), 'id_kelas' => $kelas->getKey()]);
        });
    }
}
