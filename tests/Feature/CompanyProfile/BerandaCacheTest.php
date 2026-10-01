<?php

use App\Modules\CompanyProfile\Models\Berita;
use App\Modules\CompanyProfile\Models\Galeri;
use App\Modules\CompanyProfile\Models\Prestasi;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->withoutVite();

    // Cache nyata (database/file) men-serialize nilainya, dan Laravel tidak meng-unserialize kelas PHP dari cache
    // (serializable_classes = false). Store "array" bawaan tes tidak men-serialize, jadi tiruan ini perlu agar
    // cache yang menyimpan objek model ketahuan rusak.
    config(['cache.stores.array.serialize' => true]);
    Cache::purge('array');
});

it('mensimulasikan cache yang tidak mengembalikan objek model', function () {
    Cache::put('uji', Galeri::factory()->make(), 60);

    expect(Cache::get('uji'))->not->toBeInstanceOf(Galeri::class);
});

it('menyimpan konten beranda di cache tanpa kehilangan model, atribut, dan cast-nya', function () {
    Berita::factory()->pengumuman()->create(['judul' => 'Kabar Cache', 'isi' => str_repeat('isi panjang ', 100)]);
    Galeri::factory()->create(['judul' => 'Foto Cache']);
    Prestasi::factory()->create(['judul' => 'Prestasi Cache', 'tingkat' => 'nasional']);

    foreach ([1, 2] as $pemanggilan) { // kedua: dibaca dari cache
        $berita = CacheBeranda::berita()->first();
        $galeri = CacheBeranda::galeri()->first();
        $prestasi = CacheBeranda::prestasi()->first();

        expect($berita)->toBeInstanceOf(Berita::class)
            ->judul->toBe('Kabar Cache')
            ->jenis->label()->toBe('Pengumuman')
            ->tanggal_terbit->toBeInstanceOf(DateTimeInterface::class)
            ->and(mb_strlen($berita->isi))->toBeLessThanOrEqual(400)
            ->and($galeri)->toBeInstanceOf(Galeri::class)->judul->toBe('Foto Cache')
            ->and($prestasi)->toBeInstanceOf(Prestasi::class)->tingkat->label()->toBe('Nasional');
    }

    expect(Cache::has('beranda:berita'))->toBeTrue();
});

it('menampilkan beranda dari cache yang di-serialize tanpa error', function () {
    Berita::factory()->create(['judul' => 'Berita Dari Cache']);
    Galeri::factory()->create();
    Prestasi::factory()->create();

    $this->get(route('home'))->assertOk()->assertSee('Berita Dari Cache');
    $this->get(route('home'))->assertOk()->assertSee('Berita Dari Cache'); // kali ini dari cache
});

it('mengembalikan koleksi kosong dari cache bila belum ada konten', function () {
    expect(CacheBeranda::berita())->toBeEmpty()->and(CacheBeranda::galeri())->toBeEmpty()->and(CacheBeranda::prestasi())->toBeEmpty();
    expect(CacheBeranda::berita())->toBeEmpty();
});

it('membersihkan hanya bagian yang disebut', function () {
    CacheBeranda::berita();
    CacheBeranda::galeri();

    CacheBeranda::lupakan('galeri');

    expect(Cache::has('beranda:berita'))->toBeTrue()->and(Cache::has('beranda:galeri'))->toBeFalse();

    CacheBeranda::lupakan();

    expect(Cache::has('beranda:berita'))->toBeFalse();
});
