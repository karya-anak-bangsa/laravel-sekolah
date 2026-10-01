<?php

use App\Modules\CompanyProfile\Models\Galeri;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use App\Modules\Core\Enums\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('public');
});

describe('panel admin', function () {
    it('menambah foto dalam dua ukuran WebP: besar maks. 1600 px dan thumbnail 480 px', function () {
        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.galeri.store'), ['judul' => 'Upacara Bendera', 'gambar' => UploadedFile::fake()->image('upacara.jpg', 3200, 2400)])
            ->assertRedirect(route('admin.galeri.index'))
            ->assertSessionHasNoErrors();

        $foto = Galeri::first();

        expect($foto->judul)->toBe('Upacara Bendera');

        [$lebarBesar, $tinggiBesar, $jenis] = getimagesizefromstring(Storage::disk('public')->get($foto->gambar));
        [$lebarKecil] = getimagesizefromstring(Storage::disk('public')->get($foto->gambar_kecil));

        expect($lebarBesar)->toBe(1600)->and($tinggiBesar)->toBe(1200)->and($jenis)->toBe(IMAGETYPE_WEBP)->and($lebarKecil)->toBe(480)
            ->and($foto->gambar)->toStartWith('galeri/')->and($foto->gambar_kecil)->not->toBe($foto->gambar);
    });

    it('mewajibkan foto saat menambah', function () {
        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.galeri.store'), ['judul' => 'Tanpa foto'])
            ->assertSessionHasErrors('gambar');

        expect(Galeri::count())->toBe(0);
    });

    it('menolak berkas yang bukan gambar atau terlalu besar', function (UploadedFile $berkas) {
        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.galeri.store'), ['judul' => 'X', 'gambar' => $berkas])
            ->assertSessionHasErrors('gambar');

        expect(Galeri::count())->toBe(0)->and(Storage::disk('public')->allFiles())->toBe([]);
    })->with([
        'dokumen teks' => fn () => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
        'php menyamar' => fn () => UploadedFile::fake()->create('shell.php', 10, 'image/jpeg'),
        'di atas 4 MB' => fn () => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(5000),
    ]);

    it('mewajibkan judul', function () {
        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.galeri.store'), ['judul' => '', 'gambar' => UploadedFile::fake()->image('a.jpg')])
            ->assertSessionHasErrors('judul');

        expect(Storage::disk('public')->allFiles())->toBe([]);
    });

    it('mengubah judul tanpa mengganti foto, dan mengganti foto dengan membuang berkas lama', function () {
        $admin = akun(Role::SuperAdmin);
        $this->actingAs($admin)->post(route('admin.galeri.store'), ['judul' => 'Lama', 'gambar' => UploadedFile::fake()->image('a.jpg', 800, 600)]);
        $foto = Galeri::first();
        [$besar, $kecil] = [$foto->gambar, $foto->gambar_kecil];

        $this->actingAs($admin)->put(route('admin.galeri.update', $foto), ['judul' => 'Baru'])->assertRedirect(route('admin.galeri.index'));
        expect($foto->fresh())->judul->toBe('Baru')->gambar->toBe($besar)->gambar_kecil->toBe($kecil);

        $this->actingAs($admin)->put(route('admin.galeri.update', $foto), ['judul' => 'Baru', 'gambar' => UploadedFile::fake()->image('b.jpg', 800, 600)]);

        expect($foto->fresh()->gambar)->not->toBe($besar);
        Storage::disk('public')->assertMissing([$besar, $kecil]);
        Storage::disk('public')->assertExists([$foto->fresh()->gambar, $foto->fresh()->gambar_kecil]);
    });

    it('menghapus foto secara soft delete', function () {
        $foto = Galeri::factory()->create();

        $this->actingAs(akun(Role::SuperAdmin))->delete(route('admin.galeri.destroy', $foto))->assertRedirect(route('admin.galeri.index'));

        expect(Galeri::find($foto->id_galeri))->toBeNull()->and(Galeri::withTrashed()->find($foto->id_galeri))->not->toBeNull();
    });

    it('menampilkan daftar foto terbaru lebih dulu', function () {
        Galeri::factory()->create(['judul' => 'Foto Lama', 'created_at' => now()->subDays(3)]);
        Galeri::factory()->create(['judul' => 'Foto Baru', 'created_at' => now()]);

        $this->actingAs(akun(Role::SuperAdmin))->get(route('admin.galeri.index'))->assertOk()->assertSeeInOrder(['Foto Baru', 'Foto Lama']);
    });

    it('hanya mengizinkan super_admin, menolak role lain dan tamu', function (Role $role) {
        $foto = Galeri::factory()->create(['judul' => 'Tetap']);
        $user = akun($role);

        $this->actingAs($user)->get(route('admin.galeri.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.galeri.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.galeri.store'), ['judul' => 'X', 'gambar' => UploadedFile::fake()->image('a.jpg')])->assertForbidden();
        $this->actingAs($user)->put(route('admin.galeri.update', $foto), ['judul' => 'Dibajak'])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.galeri.destroy', $foto))->assertForbidden();

        expect($foto->fresh()->judul)->toBe('Tetap')->and(Galeri::count())->toBe(1);
    })->with([Role::PetugasTu, Role::KepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel]);

    it('menolak tamu', function () {
        $this->get(route('admin.galeri.index'))->assertRedirect();
    });
});

describe('situs publik', function () {
    it('menampilkan galeri dengan tautan lightbox, terbaru lebih dulu, tanpa foto yang dihapus', function () {
        Galeri::factory()->create(['judul' => 'Foto Lama', 'created_at' => now()->subDays(3)]);
        $baru = Galeri::factory()->create(['judul' => 'Foto Baru', 'created_at' => now()]);
        Galeri::factory()->create(['judul' => 'Foto Dihapus'])->delete();

        $this->get(route('galeri'))
            ->assertOk()
            ->assertSeeInOrder(['Foto Baru', 'Foto Lama'])
            ->assertDontSee('Foto Dihapus')
            ->assertSee('class="glightbox', false)
            ->assertSee(e($baru->urlGambar()), false)
            ->assertSee(e($baru->urlGambarKecil()), false);
    });

    it('menampilkan pesan bila galeri kosong dan membagi halaman per 24 foto', function () {
        $this->get(route('galeri'))->assertOk()->assertSee('Belum ada foto');

        Galeri::factory()->count(26)->create();

        $halaman = $this->get(route('galeri'))->assertOk()->assertSee('page=2', false)->getContent();

        expect(substr_count($halaman, 'class="glightbox'))->toBe(24);
    });

    it('meloloskan judul foto sebagai teks, bukan HTML', function () {
        Galeri::factory()->create(['judul' => '"><script>alert(1)</script>']);

        $this->get(route('galeri'))->assertDontSee('<script>alert(1)</script>', false);
    });

    it('menampilkan 6 foto terbaru di beranda dan memperbaruinya setelah admin menambah foto', function () {
        $this->get(route('home'))->assertDontSee('id="galeri"', false);

        Galeri::factory()->count(8)->create();
        CacheBeranda::lupakan();

        expect(substr_count($this->get(route('home'))->getContent(), 'class="glightbox'))->toBe(6);

        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.galeri.store'), ['judul' => 'Foto Paling Baru', 'gambar' => UploadedFile::fake()->image('a.jpg', 600, 400)]);

        $this->get(route('home'))->assertSee('Foto Paling Baru');
    });
});
