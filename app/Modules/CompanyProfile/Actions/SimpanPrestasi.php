<?php

namespace App\Modules\CompanyProfile\Actions;

use App\Modules\CompanyProfile\Models\Prestasi;
use App\Modules\CompanyProfile\Services\PengolahGambar;
use Illuminate\Http\UploadedFile;
use Throwable;

/** Menyimpan prestasi dengan foto opsional (dikompres); foto lama dibuang saat diganti atau dihapus. */
class SimpanPrestasi
{
    public function __construct(private readonly PengolahGambar $gambar) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi (id_unit_sekolah, judul, nama_peraih, tingkat, tahun)
     */
    public function __invoke(Prestasi $prestasi, array $data, ?UploadedFile $berkas, bool $hapusGambar): Prestasi
    {
        $lama = $prestasi->gambar;
        $baru = $berkas ? $this->gambar->simpan($berkas, 'prestasi') : null;

        if ($baru !== null) {
            $data['gambar'] = $baru;
        } elseif ($hapusGambar) {
            $data['gambar'] = null;
        }

        try {
            $prestasi->fill($data)->save();
        } catch (Throwable $e) {
            $this->gambar->hapus($baru);

            throw $e;
        }

        if ($lama !== $prestasi->gambar) {
            $this->gambar->hapus($lama);
        }

        return $prestasi;
    }
}
