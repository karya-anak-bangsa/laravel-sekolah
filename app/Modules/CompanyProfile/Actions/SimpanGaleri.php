<?php

namespace App\Modules\CompanyProfile\Actions;

use App\Modules\CompanyProfile\Models\Galeri;
use App\Modules\CompanyProfile\Services\PengolahGambar;
use Illuminate\Http\UploadedFile;
use Throwable;

/**
 * Menyimpan foto galeri: versi besar (untuk lightbox) dan thumbnail (untuk grid) dikompres menjadi WebP.
 * Berkas lama dibuang setelah data tersimpan; berkas baru dibuang bila penyimpanan gagal.
 */
class SimpanGaleri
{
    private const LEBAR_BESAR = 1600;

    private const LEBAR_KECIL = 480;

    public function __construct(private readonly PengolahGambar $gambar) {}

    public function __invoke(Galeri $galeri, string $judul, ?UploadedFile $berkas): Galeri
    {
        $lama = [$galeri->gambar, $galeri->gambar_kecil];
        $baru = $berkas ? [
            'gambar' => $this->gambar->simpan($berkas, 'galeri', self::LEBAR_BESAR),
            'gambar_kecil' => $this->gambar->simpan($berkas, 'galeri', self::LEBAR_KECIL),
        ] : [];

        try {
            $galeri->fill(['judul' => $judul] + $baru)->save();
        } catch (Throwable $e) {
            array_map($this->gambar->hapus(...), $baru);

            throw $e;
        }

        if ($baru !== []) {
            array_map($this->gambar->hapus(...), $lama);
        }

        return $galeri;
    }
}
