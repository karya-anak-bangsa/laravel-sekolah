<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Http\Requests\PengurusRequest;
use App\Modules\CompanyProfile\Models\Pengurus;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PengurusController
{
    public function index(): View
    {
        Gate::authorize('viewAny', Pengurus::class);

        $pengurus = Pengurus::query()->with('unitSekolah')->terurut()->get();

        return view('company-profile::pengurus.index', compact('pengurus'));
    }

    public function create(): View
    {
        Gate::authorize('create', Pengurus::class);

        return view('company-profile::pengurus.form', ['pengurus' => new Pengurus, 'units' => $this->pilihanUnit()]);
    }

    public function store(PengurusRequest $request): RedirectResponse
    {
        Gate::authorize('create', Pengurus::class);

        Pengurus::create($request->untukDisimpan());

        return redirect()->route('admin.pengurus.index')->with('status', 'Pengurus berhasil ditambahkan.');
    }

    public function edit(Pengurus $pengurus): View
    {
        Gate::authorize('view', $pengurus);

        return view('company-profile::pengurus.form', ['pengurus' => $pengurus, 'units' => $this->pilihanUnit()]);
    }

    public function update(PengurusRequest $request, Pengurus $pengurus): RedirectResponse
    {
        Gate::authorize('update', $pengurus);

        $pengurus->update($request->untukDisimpan());

        return redirect()->route('admin.pengurus.index')->with('status', 'Pengurus berhasil diperbarui.');
    }

    public function destroy(Pengurus $pengurus): RedirectResponse
    {
        Gate::authorize('delete', $pengurus);

        $pengurus->delete();

        return redirect()->route('admin.pengurus.index')->with('status', 'Pengurus berhasil dihapus.');
    }

    /** @return array<int, string> id unit => nama unit */
    private function pilihanUnit(): array
    {
        return UnitSekolah::query()->orderBy('nama_unit_sekolah')->pluck('nama_unit_sekolah', 'id_unit_sekolah')->all();
    }
}
