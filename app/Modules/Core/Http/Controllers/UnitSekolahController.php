<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Actions\HapusUnitSekolah;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Http\Requests\UnitSekolahRequest;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UnitSekolahController
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', UnitSekolah::class);

        $units = UnitSekolah::query()
            ->dapatDiaksesOleh($request->user())
            ->withCount('jurusan')
            ->orderBy('jenjang')
            ->get();

        return view('core::unit-sekolah.index', compact('units'));
    }

    public function create(): View
    {
        Gate::authorize('create', UnitSekolah::class);

        return view('core::unit-sekolah.form', ['unit' => new UnitSekolah, 'jenjang' => Jenjang::cases()]);
    }

    public function store(UnitSekolahRequest $request): RedirectResponse
    {
        Gate::authorize('create', UnitSekolah::class);

        UnitSekolah::create($request->validated());

        return redirect()->route('admin.unit-sekolah.index')->with('status', 'Unit sekolah berhasil ditambahkan.');
    }

    public function edit(UnitSekolah $unitSekolah): View
    {
        Gate::authorize('view', $unitSekolah);

        $unitSekolah->load('jurusan');

        return view('core::unit-sekolah.form', ['unit' => $unitSekolah, 'jenjang' => Jenjang::cases()]);
    }

    public function update(UnitSekolahRequest $request, UnitSekolah $unitSekolah): RedirectResponse
    {
        Gate::authorize('update', $unitSekolah);

        $unitSekolah->update($request->validated());

        return redirect()->route('admin.unit-sekolah.index')->with('status', 'Unit sekolah berhasil diperbarui.');
    }

    public function destroy(UnitSekolah $unitSekolah, HapusUnitSekolah $hapus): RedirectResponse
    {
        Gate::authorize('delete', $unitSekolah);

        $hapus($unitSekolah);

        return redirect()->route('admin.unit-sekolah.index')->with('status', 'Unit sekolah berhasil dihapus.');
    }
}
