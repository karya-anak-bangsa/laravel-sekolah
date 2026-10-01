<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use App\Modules\CompanyProfile\Enums\JenisBerita;
use App\Modules\CompanyProfile\Models\Berita;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Berita dan pengumuman di situs publik: hanya yang berstatus terbit dan tanggalnya sudah tiba. */
class BeritaPublikController
{
    private const PER_HALAMAN = 9;

    public function index(Request $request): View
    {
        $jenis = JenisBerita::tryFrom((string) $request->query('jenis'));

        $berita = Berita::query()
            ->tayang()
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->terbaru()
            ->select(['id_berita', 'jenis', 'judul', 'slug', 'ringkasan', 'gambar', 'tanggal_terbit'])
            ->selectRaw('left(isi, 400) as isi')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('company-profile::berita-publik.index', ['berita' => $berita, 'jenis' => $jenis]);
    }

    public function show(string $slug): View
    {
        $berita = Berita::query()->tayang()->where('slug', $slug)->firstOrFail();

        $lainnya = Berita::query()
            ->tayang()
            ->whereKeyNot($berita->getKey())
            ->terbaru()
            ->limit(3)
            ->get(['id_berita', 'jenis', 'judul', 'slug', 'tanggal_terbit']);

        return view('company-profile::berita-publik.show', compact('berita', 'lainnya'));
    }
}
