<?php

namespace App\Modules\Kesiswaan\Http\Controllers;

use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kesiswaan\Actions\SimpanDataSiswa;
use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Enums\JenisKelamin;
use App\Modules\Kesiswaan\Enums\KebutuhanKhusus;
use App\Modules\Kesiswaan\Enums\ModaTransportasi;
use App\Modules\Kesiswaan\Enums\Pekerjaan;
use App\Modules\Kesiswaan\Enums\Pendidikan;
use App\Modules\Kesiswaan\Enums\Penghasilan;
use App\Modules\Kesiswaan\Enums\StatusSiswa;
use App\Modules\Kesiswaan\Enums\TempatTinggal;
use App\Modules\Kesiswaan\Http\Requests\SiswaRequest;
use App\Modules\Kesiswaan\Models\Siswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Master data siswa. Siswa baru masuk lewat PPDB, jadi di sini hanya daftar, detail, dan perubahan data.
 */
class SiswaController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Siswa::class);

        $user = $request->user();

        $siswa = Siswa::query()
            ->dapatDilihatOleh($user)
            ->with(['unitSekolah', 'anggotaKelas.kelas.tahunAjaran'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.addcslashes((string) $request->input('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('nama_siswa', 'like', $kata)->orWhere('nisn', 'like', $kata));
            })
            ->when($request->filled('id_unit_sekolah'), fn ($q) => $q->where('id_unit_sekolah', $request->input('id_unit_sekolah')))
            ->when($request->filled('id_kelas'), fn ($q) => $q->whereHas('anggotaKelas', fn ($a) => $a->where('id_kelas', $request->input('id_kelas'))))
            ->when($request->filled('status_siswa'), fn ($q) => $q->where('status_siswa', $request->input('status_siswa')))
            ->orderBy('nama_siswa')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('kesiswaan::siswa.index', [
            'siswa' => $siswa,
            'units' => UnitSekolah::query()->dapatDiaksesOleh($user)->orderBy('jenjang')->get(),
            'kelas' => Kelas::query()->diTahunAktif()->with('unitSekolah')->orderBy('id_unit_sekolah')->orderBy('tingkat')->orderBy('nama_kelas')->get(),
            'status' => StatusSiswa::opsi(),
        ]);
    }

    public function show(Siswa $siswa): View
    {
        Gate::authorize('view', $siswa);

        $siswa->load(['unitSekolah', 'wali', 'anggotaKelas.kelas.tahunAjaran']);

        return view('kesiswaan::siswa.show', [
            'siswa' => $siswa,
            'pilihanKelas' => Gate::allows('update', $siswa)
                ? Kelas::query()->diTahunAktif()->where('id_unit_sekolah', $siswa->id_unit_sekolah)->orderBy('tingkat')->orderBy('nama_kelas')->get()
                : collect(),
        ]);
    }

    public function edit(Siswa $siswa): View
    {
        Gate::authorize('update', $siswa);

        $siswa->load('wali');

        return view('kesiswaan::siswa.edit', [
            'siswa' => $siswa,
            'opsi' => $this->opsi(),
            'hubungan' => HubunganWali::cases(),
        ]);
    }

    public function update(SiswaRequest $request, Siswa $siswa, SimpanDataSiswa $simpan): RedirectResponse
    {
        Gate::authorize('update', $siswa);

        $simpan($siswa, $request->validated());

        return redirect()->route('admin.siswa.show', $siswa)->with('status', 'Data siswa berhasil diperbarui.');
    }

    /** @return array<string, array<string, string>> */
    private function opsi(): array
    {
        return [
            'status' => StatusSiswa::opsi(),
            'jenisKelamin' => JenisKelamin::opsi(),
            'agama' => Agama::opsi(),
            'kebutuhanKhusus' => KebutuhanKhusus::opsi(),
            'moda' => ModaTransportasi::opsi(),
            'tempatTinggal' => TempatTinggal::opsi(),
            'pendidikan' => Pendidikan::opsi(),
            'pekerjaan' => Pekerjaan::opsi(),
            'penghasilan' => Penghasilan::opsi(),
        ];
    }
}
