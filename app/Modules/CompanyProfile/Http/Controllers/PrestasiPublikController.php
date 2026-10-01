<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use App\Modules\CompanyProfile\Models\Prestasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrestasiPublikController
{
    private const PER_HALAMAN = 12;

    public function __invoke(Request $request): View
    {
        $tingkat = TingkatPrestasi::tryFrom((string) $request->query('tingkat'));

        $prestasi = Prestasi::query()
            ->with('unitSekolah')
            ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
            ->terbaru()
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('company-profile::prestasi-publik', ['prestasi' => $prestasi, 'tingkat' => $tingkat]);
    }
}
