<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Actions\HapusJurusan;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Http\Requests\JurusanRequest;
use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/** Jurusan hanya ada di unit SMK; dikelola lewat halaman unit. */
class JurusanController
{
    public function create(UnitSekolah $unitSekolah): View
    {
        $this->pastikanUnitSmk($unitSekolah, 'create');

        return view('core::jurusan.form', ['unit' => $unitSekolah, 'jurusan' => new Jurusan]);
    }

    public function store(JurusanRequest $request, UnitSekolah $unitSekolah): RedirectResponse
    {
        $this->pastikanUnitSmk($unitSekolah, 'create');

        $unitSekolah->jurusan()->create($request->validated());

        return redirect()->route('admin.unit-sekolah.edit', $unitSekolah)->with('status', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan): View
    {
        Gate::authorize('update', $jurusan);

        return view('core::jurusan.form', ['unit' => $jurusan->unitSekolah, 'jurusan' => $jurusan]);
    }

    public function update(JurusanRequest $request, Jurusan $jurusan): RedirectResponse
    {
        Gate::authorize('update', $jurusan);

        $jurusan->update($request->validated());

        return redirect()->route('admin.unit-sekolah.edit', $jurusan->id_unit_sekolah)->with('status', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan, HapusJurusan $hapus): RedirectResponse
    {
        Gate::authorize('delete', $jurusan);

        $hapus($jurusan);

        return redirect()->route('admin.unit-sekolah.edit', $jurusan->id_unit_sekolah)->with('status', 'Jurusan berhasil dihapus.');
    }

    private function pastikanUnitSmk(UnitSekolah $unit, string $aksi): void
    {
        Gate::authorize($aksi, Jurusan::class);
        abort_unless(auth()->user()->dapatMengaksesUnit($unit->getKey()), 403);
        abort_unless($unit->jenjang === Jenjang::Smk, 404);
    }
}
