<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Actions\AktifkanTahunAjaran;
use App\Modules\Core\Actions\HapusTahunAjaran;
use App\Modules\Core\Enums\Semester;
use App\Modules\Core\Http\Requests\TahunAjaranRequest;
use App\Modules\Core\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TahunAjaranController
{
    public function index(): View
    {
        Gate::authorize('viewAny', TahunAjaran::class);

        $tahunAjaran = TahunAjaran::query()->orderByDesc('nama_tahun_ajaran')->get();

        return view('core::tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create(): View
    {
        Gate::authorize('create', TahunAjaran::class);

        return view('core::tahun-ajaran.form', ['tahunAjaran' => new TahunAjaran, 'semester' => Semester::cases()]);
    }

    public function store(TahunAjaranRequest $request): RedirectResponse
    {
        Gate::authorize('create', TahunAjaran::class);

        TahunAjaran::create($request->validated() + ['is_aktif' => false]);

        return redirect()->route('admin.tahun-ajaran.index')->with('status', 'Tahun ajaran berhasil ditambahkan. Klik "Aktifkan" bila ingin memakainya sekarang.');
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        Gate::authorize('view', $tahunAjaran);

        return view('core::tahun-ajaran.form', ['tahunAjaran' => $tahunAjaran, 'semester' => Semester::cases()]);
    }

    public function update(TahunAjaranRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        Gate::authorize('update', $tahunAjaran);

        $tahunAjaran->update($request->validated());

        return redirect()->route('admin.tahun-ajaran.index')->with('status', 'Tahun ajaran berhasil diperbarui.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran, AktifkanTahunAjaran $aktifkan): RedirectResponse
    {
        Gate::authorize('update', $tahunAjaran);

        $aktifkan($tahunAjaran);

        return redirect()->route('admin.tahun-ajaran.index')->with('status', "Tahun ajaran {$tahunAjaran->nama_tahun_ajaran} sekarang aktif.");
    }

    public function destroy(TahunAjaran $tahunAjaran, HapusTahunAjaran $hapus): RedirectResponse
    {
        Gate::authorize('delete', $tahunAjaran);

        $hapus($tahunAjaran);

        return redirect()->route('admin.tahun-ajaran.index')->with('status', 'Tahun ajaran berhasil dihapus.');
    }
}
