<?php

namespace App\Modules\Kesiswaan\Http\Controllers;

use App\Modules\Core\Models\Kelas;
use App\Modules\Kesiswaan\Actions\TempatkanSiswaDiKelas;
use App\Modules\Kesiswaan\Http\Requests\PenempatanKelasRequest;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use App\Modules\Kesiswaan\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PenempatanKelasController
{
    public function store(PenempatanKelasRequest $request, Siswa $siswa, TempatkanSiswaDiKelas $tempatkan): RedirectResponse
    {
        Gate::authorize('update', $siswa);

        $kelas = Kelas::findOrFail($request->validated('id_kelas'));
        $tempatkan($siswa, $kelas);

        return redirect()->route('admin.siswa.show', $siswa)->with('status', "{$siswa->nama_siswa} ditempatkan di kelas {$kelas->nama_kelas}.");
    }

    public function destroy(Siswa $siswa, AnggotaKelas $anggotaKelas): RedirectResponse
    {
        Gate::authorize('update', $siswa);
        abort_unless($anggotaKelas->id_siswa === $siswa->getKey(), 404);

        $anggotaKelas->delete();

        return redirect()->route('admin.siswa.show', $siswa)->with('status', 'Siswa dikeluarkan dari kelas.');
    }
}
