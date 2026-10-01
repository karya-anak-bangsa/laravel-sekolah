<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Models\Pengurus;
use App\Modules\CompanyProfile\Services\PengaturanSitus;
use Illuminate\Contracts\View\View;

class TentangController
{
    public function __invoke(PengaturanSitus $pengaturan): View
    {
        $pengurus = Pengurus::query()->with('unitSekolah')->terurut()->get()
            ->groupBy(fn (Pengurus $item) => $item->unitSekolah?->nama_unit_sekolah ?? 'Yayasan');

        return view('company-profile::tentang', [
            'paragrafSejarah' => $this->pecah($pengaturan->get('sejarah'), '/\R{2,}/'),
            'butirMisi' => $this->pecah($pengaturan->get('misi'), '/\R+/'),
            'pengurus' => $pengurus,
        ]);
    }

    /** @return list<string> */
    private function pecah(?string $teks, string $pola): array
    {
        return array_values(array_filter(array_map('trim', preg_split($pola, (string) $teks))));
    }
}
