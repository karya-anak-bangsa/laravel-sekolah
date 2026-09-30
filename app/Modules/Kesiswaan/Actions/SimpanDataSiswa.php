<?php

namespace App\Modules\Kesiswaan\Actions;

use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Models\Siswa;
use App\Modules\Kesiswaan\Models\WaliSiswa;
use Illuminate\Support\Facades\DB;

class SimpanDataSiswa
{
    private const KOLOM_WALI = ['nama_wali_siswa', 'pendidikan', 'pekerjaan', 'penghasilan', 'no_hp', 'agama', 'alamat'];

    /**
     * Menyimpan identitas siswa dan data ayah/ibu/wali dalam satu transaksi.
     * Data wali yang sudah ada diperbarui di tempat (dipakai bersama kakak-adik); wali tanpa nama dilepas dari siswa.
     *
     * @param  array<string, mixed>  $data  hasil validasi SiswaRequest (kunci "wali" berisi ayah/ibu/wali)
     */
    public function __invoke(Siswa $siswa, array $data): Siswa
    {
        $wali = $data['wali'] ?? [];
        unset($data['wali']);

        return DB::transaction(function () use ($siswa, $data, $wali) {
            $siswa->update($data);
            $siswa->load('wali');

            foreach (HubunganWali::cases() as $hubungan) {
                $this->simpanWali($siswa, $hubungan, $wali[$hubungan->value] ?? []);
            }

            return $siswa->load('wali');
        });
    }

    private function simpanWali(Siswa $siswa, HubunganWali $hubungan, array $isian): void
    {
        $ada = $siswa->waliDengan($hubungan);
        $atribut = array_intersect_key($isian, array_flip(self::KOLOM_WALI));

        if (blank($atribut['nama_wali_siswa'] ?? null)) {
            if ($ada) {
                $siswa->wali()->detach($ada->getKey());
            }

            return;
        }

        $pivot = ['hubungan' => $hubungan->value, 'hubungan_keluarga' => $hubungan === HubunganWali::Wali ? ($isian['hubungan_keluarga'] ?? null) : null];

        if ($ada) {
            $ada->update($atribut);
            $siswa->wali()->updateExistingPivot($ada->getKey(), ['hubungan_keluarga' => $pivot['hubungan_keluarga']]);

            return;
        }

        $baru = WaliSiswa::create($atribut);
        $siswa->wali()->attach($baru->getKey(), $pivot);
    }
}
