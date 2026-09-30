<?php

namespace App\Modules\Kesiswaan\Http\Controllers;

use App\Modules\Core\Models\Kelas;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/** Daftar anggota (siswa) sebuah kelas. */
class AnggotaKelasController
{
    public function index(Kelas $kelas): View
    {
        Gate::authorize('viewAnggota', $kelas);

        $kelas->load(['unitSekolah', 'jurusan', 'tahunAjaran', 'waliKelas']);

        $anggota = $kelas->anggotaKelas()
            ->with('siswa')
            ->get()
            ->filter(fn ($a) => $a->siswa !== null) // siswa di luar unit pengguna/terhapus tersaring scope
            ->sortBy(fn ($a) => $a->siswa->nama_siswa)
            ->values();

        return view('kesiswaan::kelas.anggota', compact('kelas', 'anggota'));
    }
}
