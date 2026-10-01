<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Actions\SimpanBerita;
use App\Modules\CompanyProfile\Enums\JenisBerita;
use App\Modules\CompanyProfile\Enums\StatusBerita;
use App\Modules\CompanyProfile\Http\Requests\BeritaRequest;
use App\Modules\CompanyProfile\Models\Berita;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BeritaController
{
    private const PER_HALAMAN = 25;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Berita::class);

        $berita = Berita::query()
            ->when($request->filled('q'), fn ($q) => $q->where('judul', 'like', '%'.addcslashes((string) $request->input('q'), '%_\\').'%'))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->input('jenis')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByRaw('tanggal_terbit is null desc') // draf (belum bertanggal) di atas
            ->terbaru()
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('company-profile::berita.index', [
            'berita' => $berita,
            'jenis' => JenisBerita::opsi(),
            'status' => StatusBerita::opsi(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Berita::class);

        return view('company-profile::berita.form', $this->dataForm(new Berita(['jenis' => JenisBerita::Berita, 'status' => StatusBerita::Draf])));
    }

    public function store(BeritaRequest $request, SimpanBerita $simpan): RedirectResponse
    {
        Gate::authorize('create', Berita::class);

        $simpan(new Berita, $request->dataBerita(), $request->file('gambar'), false, $request->user()->id_pegawai);
        CacheBeranda::lupakan();

        return redirect()->route('admin.berita.index')->with('status', 'Berita berhasil disimpan.');
    }

    public function edit(Berita $berita): View
    {
        Gate::authorize('view', $berita);

        return view('company-profile::berita.form', $this->dataForm($berita));
    }

    public function update(BeritaRequest $request, Berita $berita, SimpanBerita $simpan): RedirectResponse
    {
        Gate::authorize('update', $berita);

        $simpan($berita, $request->dataBerita(), $request->file('gambar'), $request->boolean('hapus_gambar'), $request->user()->id_pegawai);
        CacheBeranda::lupakan();

        return redirect()->route('admin.berita.index')->with('status', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $berita): RedirectResponse
    {
        Gate::authorize('delete', $berita);

        $berita->delete();
        CacheBeranda::lupakan();

        return redirect()->route('admin.berita.index')->with('status', 'Berita berhasil dihapus.');
    }

    private function dataForm(Berita $berita): array
    {
        return ['berita' => $berita, 'jenis' => JenisBerita::opsi(), 'status' => StatusBerita::opsi()];
    }
}
