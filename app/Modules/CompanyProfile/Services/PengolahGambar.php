<?php

namespace App\Modules\CompanyProfile\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Mengecilkan dan mengompres gambar unggahan (GD bawaan PHP) menjadi WebP di disk public.
 * Dipakai berita, dan nanti galeri/prestasi. Validasi jenis dan ukuran berkas dilakukan di FormRequest.
 */
class PengolahGambar
{
    private const KUALITAS = 80;

    /** Batas piksel sebelum didekode, agar gambar raksasa tidak menghabiskan memori. */
    private const PIKSEL_MAKS = 40_000_000;

    /**
     * @return string path relatif di disk public, mis. "berita/0c1d....webp"
     */
    public function simpan(UploadedFile $berkas, string $folder, int $lebarMaks = 1200): string
    {
        $ukuran = @getimagesize($berkas->getRealPath());

        if ($ukuran === false || $ukuran[0] * $ukuran[1] > self::PIKSEL_MAKS) {
            throw new RuntimeException('Gambar tidak dapat diproses.');
        }

        $gambar = @imagecreatefromstring((string) file_get_contents($berkas->getRealPath()));

        if ($gambar === false) {
            throw new RuntimeException('Gambar tidak dapat dibaca.');
        }

        $gambar = $this->putarSesuaiExif($gambar, $berkas->getRealPath());
        $gambar = $this->kecilkan($gambar, $lebarMaks);

        ob_start();
        imagewebp($gambar, null, self::KUALITAS);
        $isi = (string) ob_get_clean();

        $path = trim($folder, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $isi);

        return $path;
    }

    public function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function kecilkan(GdImage $gambar, int $lebarMaks): GdImage
    {
        imagepalettetotruecolor($gambar);
        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);

        $lebar = imagesx($gambar);

        if ($lebar <= $lebarMaks) {
            return $gambar;
        }

        return imagescale($gambar, $lebarMaks) ?: $gambar;
    }

    /** Foto dari HP sering menyimpan arah putar di EXIF; terapkan agar tidak tampil miring. */
    private function putarSesuaiExif(GdImage $gambar, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($path);
        $derajat = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $derajat === 0 ? $gambar : (imagerotate($gambar, $derajat, 0) ?: $gambar);
    }
}
