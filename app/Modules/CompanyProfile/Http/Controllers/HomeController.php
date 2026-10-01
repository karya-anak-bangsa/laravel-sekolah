<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Support\CacheBeranda;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Models\UnitSekolah;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(): View
    {
        $units = UnitSekolah::query()
            ->with(['jurusan' => fn ($query) => $query->orderBy('nama_jurusan')])
            ->get()
            ->sortBy(fn (UnitSekolah $unit) => array_search($unit->jenjang, Jenjang::cases(), true)) // SMP lebih dulu dari SMK
            ->values();

        return view('company-profile::home', [
            'units' => $units, 'beritaTerbaru' => CacheBeranda::berita(),
            'galeriTerbaru' => CacheBeranda::galeri(),
            'prestasiTerbaru' => CacheBeranda::prestasi(),
        ]);
    }
}
