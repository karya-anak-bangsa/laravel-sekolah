<?php

namespace App\Modules\CompanyProfile\Actions;

use App\Modules\CompanyProfile\Enums\StatusBerita;
use App\Modules\CompanyProfile\Models\Berita;
use App\Modules\CompanyProfile\Services\PengolahGambar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Membuat atau mengubah berita: slug (tetap setelah dibuat agar tautan lama tidak putus), tanggal terbit,
 * dan gambar (dikompres, gambar lama dibuang setelah data tersimpan).
 */
class SimpanBerita
{
    public function __construct(private readonly PengolahGambar $gambar) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi (jenis, judul, ringkasan, isi, status, tanggal_terbit)
     */
    public function __invoke(Berita $berita, array $data, ?UploadedFile $gambarBaru, bool $hapusGambar, ?int $idPegawaiPenulis): Berita
    {
        $gambarLama = $berita->gambar;
        $pathBaru = $gambarBaru ? $this->gambar->simpan($gambarBaru, 'berita') : null;

        $data['status'] = StatusBerita::from($data['status']);
        $data['tanggal_terbit'] = $data['status'] === StatusBerita::Terbit
            ? ($data['tanggal_terbit'] ?? $berita->tanggal_terbit ?? now())
            : null;

        if ($pathBaru !== null) {
            $data['gambar'] = $pathBaru;
        } elseif ($hapusGambar) {
            $data['gambar'] = null;
        }

        try {
            DB::transaction(function () use ($berita, $data, $idPegawaiPenulis) {
                if (! $berita->exists) {
                    $data['slug'] = $this->slugUnik($data['judul']);
                    $data['id_pegawai_penulis'] = $idPegawaiPenulis;
                }

                $berita->fill($data)->save();
            });
        } catch (Throwable $e) {
            $this->gambar->hapus($pathBaru); // jangan tinggalkan berkas yatim

            throw $e;
        }

        if (($pathBaru !== null || $hapusGambar) && $gambarLama !== $berita->gambar) {
            $this->gambar->hapus($gambarLama);
        }

        return $berita;
    }

    private function slugUnik(string $judul): string
    {
        $dasar = Str::limit(Str::slug($judul) ?: 'berita', 200, '');
        $slug = $dasar;

        for ($i = 2; Berita::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $dasar.'-'.$i;
        }

        return $slug;
    }
}
