<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Http\Requests\KelasRequest;
use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\TahunAjaran;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class KelasController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Kelas::class);

        // Tanpa parameter tahun ajaran -> tampilkan tahun ajaran aktif; "Semua" mengirim nilai kosong.
        $idTahun = $request->has('id_tahun_ajaran')
            ? $request->input('id_tahun_ajaran')
            : TahunAjaran::aktif()->value('id_tahun_ajaran');

        $kelas = Kelas::query()
            ->with(['unitSekolah', 'jurusan', 'tahunAjaran', 'waliKelas'])
            ->when($request->filled('id_unit_sekolah'), fn ($q) => $q->where('id_unit_sekolah', $request->input('id_unit_sekolah')))
            ->when(filled($idTahun), fn ($q) => $q->where('id_tahun_ajaran', $idTahun))
            ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->integer('tingkat')))
            ->orderBy('id_unit_sekolah')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('core::kelas.index', [
            'kelas' => $kelas,
            'units' => UnitSekolah::query()->dapatDiaksesOleh($request->user())->orderBy('jenjang')->get(),
            'tahunAjaran' => TahunAjaran::query()->orderByDesc('nama_tahun_ajaran')->get(),
            'idTahun' => $idTahun,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Kelas::class);

        $kelas = new Kelas(['id_tahun_ajaran' => TahunAjaran::aktif()->value('id_tahun_ajaran')]);

        return view('core::kelas.form', ['kelas' => $kelas] + $this->opsiForm($request));
    }

    public function store(KelasRequest $request): RedirectResponse
    {
        Gate::authorize('create', Kelas::class);

        Kelas::create($request->validated());

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Request $request, Kelas $kelas): View
    {
        Gate::authorize('view', $kelas);

        return view('core::kelas.form', ['kelas' => $kelas] + $this->opsiForm($request));
    }

    public function update(KelasRequest $request, Kelas $kelas): RedirectResponse
    {
        Gate::authorize('update', $kelas);

        $kelas->update($request->validated());

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        Gate::authorize('delete', $kelas);

        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil dihapus.');
    }

    /** Pilihan untuk form; Jurusan dan Pegawai sudah tersaring unit pengguna oleh global scope. */
    private function opsiForm(Request $request): array
    {
        return [
            'units' => UnitSekolah::query()->dapatDiaksesOleh($request->user())->orderBy('jenjang')->get(),
            'tahunAjaran' => TahunAjaran::query()->orderByDesc('nama_tahun_ajaran')->get(),
            'jurusan' => Jurusan::query()->with('unitSekolah')->orderBy('nama_jurusan')->get(),
            'guru' => Pegawai::query()->with('unitSekolah')->where('jenis_pegawai', JenisPegawai::Guru)->orderBy('nama_pegawai')->get(),
        ];
    }
}
