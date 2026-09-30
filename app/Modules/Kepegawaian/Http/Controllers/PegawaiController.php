<?php

namespace App\Modules\Kepegawaian\Http\Controllers;

use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kepegawaian\Actions\HapusPegawai;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Http\Requests\PegawaiRequest;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PegawaiController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Pegawai::class);

        $pegawai = Pegawai::query()
            ->with(['unitSekolah', 'user' => fn ($q) => $q->withTrashed()])
            ->when($request->filled('q'), fn ($q) => $q->where('nama_pegawai', 'like', '%'.addcslashes((string) $request->input('q'), '%_\\').'%'))
            ->when($request->filled('id_unit_sekolah'), fn ($q) => $q->where('id_unit_sekolah', $request->input('id_unit_sekolah')))
            ->when($request->filled('jenis_pegawai'), fn ($q) => $q->where('jenis_pegawai', $request->input('jenis_pegawai')))
            ->orderBy('nama_pegawai')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('kepegawaian::pegawai.index', [
            'pegawai' => $pegawai,
            'units' => UnitSekolah::query()->dapatDiaksesOleh($request->user())->orderBy('jenjang')->get(),
            'jenis' => JenisPegawai::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Pegawai::class);

        return view('kepegawaian::pegawai.form', ['pegawai' => new Pegawai] + $this->opsiForm($request));
    }

    public function store(PegawaiRequest $request): RedirectResponse
    {
        Gate::authorize('create', Pegawai::class);

        Pegawai::create($request->validated());

        return redirect()->route('admin.pegawai.index')->with('status', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Request $request, Pegawai $pegawai): View
    {
        Gate::authorize('view', $pegawai);

        return view('kepegawaian::pegawai.form', ['pegawai' => $pegawai] + $this->opsiForm($request));
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai): RedirectResponse
    {
        Gate::authorize('update', $pegawai);

        $pegawai->update($request->validated() + ['id_unit_sekolah' => null]);

        return redirect()->route('admin.pegawai.index')->with('status', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai, HapusPegawai $hapus): RedirectResponse
    {
        Gate::authorize('delete', $pegawai);

        $hapus($pegawai);

        return redirect()->route('admin.pegawai.index')->with('status', 'Pegawai berhasil dihapus.');
    }

    private function opsiForm(Request $request): array
    {
        return [
            'units' => UnitSekolah::query()->dapatDiaksesOleh($request->user())->orderBy('jenjang')->get(),
            'jenis' => JenisPegawai::cases(),
        ];
    }
}
