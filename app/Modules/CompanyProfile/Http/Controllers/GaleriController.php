<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Actions\SimpanGaleri;
use App\Modules\CompanyProfile\Http\Requests\GaleriRequest;
use App\Modules\CompanyProfile\Models\Galeri;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class GaleriController
{
    private const PER_HALAMAN = 24;

    public function index(): View
    {
        Gate::authorize('viewAny', Galeri::class);

        return view('company-profile::galeri.index', ['galeri' => Galeri::query()->terbaru()->paginate(self::PER_HALAMAN)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Galeri::class);

        return view('company-profile::galeri.form', ['galeri' => new Galeri]);
    }

    public function store(GaleriRequest $request, SimpanGaleri $simpan): RedirectResponse
    {
        Gate::authorize('create', Galeri::class);

        $simpan(new Galeri, $request->validated('judul'), $request->file('gambar'));
        CacheBeranda::lupakan('galeri');

        return redirect()->route('admin.galeri.index')->with('status', 'Foto berhasil ditambahkan.');
    }

    public function edit(Galeri $galeri): View
    {
        Gate::authorize('view', $galeri);

        return view('company-profile::galeri.form', ['galeri' => $galeri]);
    }

    public function update(GaleriRequest $request, Galeri $galeri, SimpanGaleri $simpan): RedirectResponse
    {
        Gate::authorize('update', $galeri);

        $simpan($galeri, $request->validated('judul'), $request->file('gambar'));
        CacheBeranda::lupakan('galeri');

        return redirect()->route('admin.galeri.index')->with('status', 'Foto berhasil diperbarui.');
    }

    public function destroy(Galeri $galeri): RedirectResponse
    {
        Gate::authorize('delete', $galeri);

        $galeri->delete();
        CacheBeranda::lupakan('galeri');

        return redirect()->route('admin.galeri.index')->with('status', 'Foto berhasil dihapus.');
    }
}
