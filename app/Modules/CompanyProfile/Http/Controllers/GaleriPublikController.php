<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Models\Galeri;
use Illuminate\Contracts\View\View;

class GaleriPublikController
{
    private const PER_HALAMAN = 24;

    public function __invoke(): View
    {
        return view('company-profile::galeri-publik', ['galeri' => Galeri::query()->terbaru()->paginate(self::PER_HALAMAN)]);
    }
}
