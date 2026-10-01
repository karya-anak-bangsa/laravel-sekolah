<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Actions\SimpanPrestasi;
use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use App\Modules\CompanyProfile\Http\Requests\PrestasiRequest;
use App\Modules\CompanyProfile\Models\Prestasi;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PrestasiController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Prestasi::class);

        $prestasi = Prestasi::query()
            ->with('unitSekolah')
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.addcslashes((string) $request->input('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('judul', 'like', $kata)->orWhere('nama_peraih', 'like', $kata));
            })
            ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->input('tingkat')))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', (int) $request->input('tahun')))
            ->terbaru()
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('company-profile::prestasi.index', ['prestasi' => $prestasi, 'tingkat' => TingkatPrestasi::opsi()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Prestasi::class);

        return view('company-profile::prestasi.form', $this->dataForm(new Prestasi(['tahun' => now()->year])));
    }

    public function store(PrestasiRequest $request, SimpanPrestasi $simpan): RedirectResponse
    {
        Gate::authorize('create', Prestasi::class);

        $simpan(new Prestasi, $request->dataPrestasi(), $request->file('gambar'), false);
        CacheBeranda::lupakan('prestasi');

        return redirect()->route('admin.prestasi.index')->with('status', 'Prestasi berhasil ditambahkan.');
    }

    public function edit(Prestasi $prestasi): View
    {
        Gate::authorize('view', $prestasi);

        return view('company-profile::prestasi.form', $this->dataForm($prestasi));
    }

    public function update(PrestasiRequest $request, Prestasi $prestasi, SimpanPrestasi $simpan): RedirectResponse
    {
        Gate::authorize('update', $prestasi);

        $simpan($prestasi, $request->dataPrestasi(), $request->file('gambar'), $request->boolean('hapus_gambar'));
        CacheBeranda::lupakan('prestasi');

        return redirect()->route('admin.prestasi.index')->with('status', 'Prestasi berhasil diperbarui.');
    }

    public function destroy(Prestasi $prestasi): RedirectResponse
    {
        Gate::authorize('delete', $prestasi);

        $prestasi->delete();
        CacheBeranda::lupakan('prestasi');

        return redirect()->route('admin.prestasi.index')->with('status', 'Prestasi berhasil dihapus.');
    }

    private function dataForm(Prestasi $prestasi): array
    {
        return [
            'prestasi' => $prestasi,
            'tingkat' => TingkatPrestasi::opsi(),
            'units' => UnitSekolah::query()->orderBy('nama_unit_sekolah')->pluck('nama_unit_sekolah', 'id_unit_sekolah')->all(),
        ];
    }
}
