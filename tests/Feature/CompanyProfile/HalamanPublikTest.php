<?php

use App\Modules\CompanyProfile\Models\Pengurus;
use App\Modules\CompanyProfile\Services\PengaturanSitus;
use App\Modules\Core\Database\Seeders\UnitSekolahSeeder;

beforeEach(function () {
    $this->withoutVite();
});

it('menampilkan beranda, tentang, dan kontak untuk tamu', function (string $rute) {
    $this->get(route($rute))->assertOk();
})->with(['home', 'tentang', 'kontak']);

it('menampilkan menu publik Tentang dan Kontak', function () {
    $this->get(route('home'))->assertSee(route('tentang'))->assertSee(route('kontak'));
});

it('menampilkan unit sekolah dan jurusan SMK di beranda, SMP lebih dulu', function () {
    $this->seed(UnitSekolahSeeder::class);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['SMP Puspita Bangsa', 'SMK Puspita Bangsa', 'Bisnis Manajemen', 'Pariwisata'])
        ->assertSee('Rekayasa Perangkat Lunak');
});

it('menampilkan sejarah per paragraf dan misi per butir di halaman tentang', function () {
    app(PengaturanSitus::class)->simpan([
        'sejarah' => "Paragraf satu.\n\nParagraf dua.",
        'visi' => 'Visi uji.',
        'misi' => "Misi pertama\nMisi kedua",
    ]);

    $this->get(route('tentang'))
        ->assertOk()
        ->assertSee('<p>Paragraf satu.</p>', false)
        ->assertSee('<p>Paragraf dua.</p>', false)
        ->assertSee('Visi uji.')
        ->assertSee('<li class="mb-2">Misi pertama</li>', false)
        ->assertSee('<li class="mb-2">Misi kedua</li>', false);
});

it('menampilkan struktur organisasi dikelompokkan per tingkat, dan menyembunyikannya bila kosong', function () {
    $this->get(route('tentang'))->assertDontSee('Struktur Organisasi');

    Pengurus::create(['nama_pengurus' => 'Ibu Ketua', 'jabatan' => 'Ketua Yayasan', 'urutan' => 1]);

    $this->get(route('tentang'))->assertSee('Struktur Organisasi')->assertSee('Ibu Ketua')->assertSee('Ketua Yayasan');
});

it('tidak menampilkan pengurus yang sudah dihapus', function () {
    Pengurus::create(['nama_pengurus' => 'Sudah Pergi', 'jabatan' => 'Ketua'])->delete();

    $this->get(route('tentang'))->assertDontSee('Sudah Pergi');
});

it('meloloskan isi pengaturan sebagai teks, bukan HTML', function () {
    app(PengaturanSitus::class)->simpan(['nama' => '<script>alert(1)</script>', 'sejarah' => '<img src=x onerror=alert(1)>']);

    $this->get(route('tentang'))
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});

it('menyembunyikan peta dan kartu kontak yang dikosongkan', function () {
    app(PengaturanSitus::class)->simpan(['peta_embed_url' => '', 'jam_layanan' => '']);

    $this->get(route('kontak'))->assertOk()->assertDontSee('<iframe', false)->assertDontSee('Jam Layanan');
});
