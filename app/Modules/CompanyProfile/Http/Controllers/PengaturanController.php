<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Http\Requests\PengaturanRequest;
use App\Modules\CompanyProfile\Models\Pengaturan;
use App\Modules\CompanyProfile\Services\PengaturanSitus;
use App\Modules\CompanyProfile\Support\KatalogPengaturan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PengaturanController
{
    public function edit(PengaturanSitus $pengaturan): View
    {
        Gate::authorize('view', Pengaturan::class);

        return view('company-profile::pengaturan.edit', [
            'kelompok' => KatalogPengaturan::perKelompok(),
            'judulKelompok' => KatalogPengaturan::KELOMPOK,
            'nilai' => $pengaturan->semua(),
        ]);
    }

    public function update(PengaturanRequest $request, PengaturanSitus $pengaturan): RedirectResponse
    {
        Gate::authorize('update', Pengaturan::class);

        $pengaturan->simpan($request->validated());

        return redirect()->route('admin.pengaturan.edit')->with('status', 'Pengaturan situs berhasil disimpan.');
    }
}
