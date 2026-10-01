<?php

use App\Modules\CompanyProfile\Enums\StatusBerita;
use App\Modules\CompanyProfile\Models\Berita;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use App\Modules\Core\Enums\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('public');
});

/** Isian form berita yang valid; $ubah menimpa sebagian. */
function isianBerita(array $ubah = []): array
{
    return array_merge([
        'jenis' => 'berita',
        'judul' => 'Juara Lomba Kompetensi Siswa',
        'ringkasan' => 'Siswa kita meraih juara.',
        'isi' => "Paragraf pertama.\n\nParagraf kedua.",
        'status' => 'terbit',
    ], $ubah);
}

describe('panel admin', function () {
    it('menampilkan daftar dengan status tayang, terjadwal, dan draf', function () {
        Berita::factory()->create(['judul' => 'Sudah Tayang']);
        Berita::factory()->terjadwal()->create(['judul' => 'Akan Tayang']);
        Berita::factory()->draf()->create(['judul' => 'Masih Draf']);

        $this->actingAs(akun(Role::PetugasTu))
            ->get(route('admin.berita.index'))
            ->assertOk()
            ->assertSeeInOrder(['Masih Draf', 'Draf', 'Akan Tayang', 'Terjadwal', 'Sudah Tayang', 'Tayang']);
    });

    it('memfilter daftar menurut jenis, status, dan kata kunci', function () {
        Berita::factory()->create(['judul' => 'Kabar Prestasi']);
        Berita::factory()->pengumuman()->create(['judul' => 'Libur Semester']);
        Berita::factory()->draf()->create(['judul' => 'Rencana Rahasia']);

        $admin = akun(Role::PetugasTu);

        $this->actingAs($admin)->get(route('admin.berita.index', ['jenis' => 'pengumuman']))
            ->assertSee('Libur Semester')->assertDontSee('Kabar Prestasi');
        $this->actingAs($admin)->get(route('admin.berita.index', ['status' => 'draf']))
            ->assertSee('Rencana Rahasia')->assertDontSee('Kabar Prestasi');
        $this->actingAs($admin)->get(route('admin.berita.index', ['q' => 'Prestasi']))
            ->assertSee('Kabar Prestasi')->assertDontSee('Libur Semester');
    });

    it('membuat berita dengan slug dari judul dan penulis dari pegawai yang login', function () {
        $tu = akun(Role::PetugasTu);

        $this->actingAs($tu)->post(route('admin.berita.store'), isianBerita())->assertRedirect(route('admin.berita.index'));

        $berita = Berita::firstWhere('judul', 'Juara Lomba Kompetensi Siswa');

        expect($berita->slug)->toBe('juara-lomba-kompetensi-siswa')
            ->and($berita->id_pegawai_penulis)->toBe($tu->id_pegawai)
            ->and($berita->status)->toBe(StatusBerita::Terbit)
            ->and($berita->tanggal_terbit)->not->toBeNull()
            ->and($berita->sudahTayang())->toBeTrue();
    });

    it('menambah akhiran angka bila slug sudah dipakai, termasuk oleh berita yang sudah dihapus', function () {
        Berita::factory()->create(['judul' => 'Judul Sama', 'slug' => 'judul-sama'])->delete();
        $admin = akun(Role::SuperAdmin);

        $this->actingAs($admin)->post(route('admin.berita.store'), isianBerita(['judul' => 'Judul Sama']));
        $this->actingAs($admin)->post(route('admin.berita.store'), isianBerita(['judul' => 'Judul Sama']));

        expect(Berita::pluck('slug')->all())->toBe(['judul-sama-2', 'judul-sama-3']);
    });

    it('menyimpan draf tanpa tanggal terbit, dan mengabaikan tanggal yang dikirim untuk draf', function () {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita(['status' => 'draf', 'tanggal_terbit' => '2030-01-01 10:00']));

        expect(Berita::first())->status->toBe(StatusBerita::Draf)->tanggal_terbit->toBeNull();
    });

    it('menjadwalkan berita dengan tanggal mendatang sehingga belum tayang', function () {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita(['tanggal_terbit' => now()->addDays(5)->format('Y-m-d\TH:i')]));

        $berita = Berita::first();

        expect($berita->status)->toBe(StatusBerita::Terbit)->and($berita->sudahTayang())->toBeFalse();

        $this->get(route('berita.show', $berita->slug))->assertNotFound();
    });

    it('mempertahankan slug saat judul diubah', function () {
        $berita = Berita::factory()->create(['judul' => 'Judul Lama', 'slug' => 'judul-lama']);

        $this->actingAs(akun(Role::PetugasTu))
            ->put(route('admin.berita.update', $berita), isianBerita(['judul' => 'Judul Baru Sekali']))
            ->assertRedirect(route('admin.berita.index'));

        expect($berita->fresh())->judul->toBe('Judul Baru Sekali')->slug->toBe('judul-lama');
    });

    it('menghapus berita secara soft delete', function () {
        $berita = Berita::factory()->create();

        $this->actingAs(akun(Role::PetugasTu))->delete(route('admin.berita.destroy', $berita))->assertRedirect(route('admin.berita.index'));

        expect(Berita::find($berita->id_berita))->toBeNull()
            ->and(Berita::withTrashed()->find($berita->id_berita))->not->toBeNull();
    });

    it('memvalidasi isian berita', function (array $ubah, string $kolom) {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita($ubah))
            ->assertSessionHasErrors($kolom);

        expect(Berita::count())->toBe(0);
    })->with([
        'judul wajib' => [['judul' => ''], 'judul'],
        'judul terlalu panjang' => [['judul' => str_repeat('a', 201)], 'judul'],
        'isi wajib' => [['isi' => ''], 'isi'],
        'ringkasan terlalu panjang' => [['ringkasan' => str_repeat('a', 301)], 'ringkasan'],
        'jenis tidak dikenal' => [['jenis' => 'gosip'], 'jenis'],
        'status tidak dikenal' => [['status' => 'rahasia'], 'status'],
        'tanggal tidak valid' => [['tanggal_terbit' => 'kapan-kapan'], 'tanggal_terbit'],
    ]);

    it('hanya mengizinkan petugas TU dan super_admin, menolak role lain', function (Role $role) {
        $berita = Berita::factory()->create(['judul' => 'Tetap']);
        $user = akun($role);

        $this->actingAs($user)->get(route('admin.berita.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.berita.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.berita.store'), isianBerita())->assertForbidden();
        $this->actingAs($user)->get(route('admin.berita.edit', $berita))->assertForbidden();
        $this->actingAs($user)->put(route('admin.berita.update', $berita), isianBerita(['judul' => 'Dibajak']))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.berita.destroy', $berita))->assertForbidden();

        expect($berita->fresh()->judul)->toBe('Tetap')->and(Berita::count())->toBe(1);
    })->with([Role::KepalaSekolah, Role::WakilKepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel, Role::GuruPiket]);

    it('menolak tamu', function () {
        $this->get(route('admin.berita.index'))->assertRedirect();
        $this->post(route('admin.berita.store'), isianBerita())->assertRedirect();
    });
});

describe('gambar', function () {
    it('mengompres gambar besar menjadi WebP berlebar maksimal 1200 piksel', function () {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita(['gambar' => UploadedFile::fake()->image('foto.jpg', 3000, 2000)]))
            ->assertSessionHasNoErrors();

        $path = Berita::first()->gambar;

        expect($path)->toStartWith('berita/')->toEndWith('.webp');
        Storage::disk('public')->assertExists($path);

        [$lebar, $tinggi, $jenis] = getimagesizefromstring(Storage::disk('public')->get($path));

        expect($lebar)->toBe(1200)->and($tinggi)->toBe(800)->and($jenis)->toBe(IMAGETYPE_WEBP);
    });

    it('tidak memperbesar gambar kecil', function () {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita(['gambar' => UploadedFile::fake()->image('kecil.png', 400, 300)]));

        [$lebar] = getimagesizefromstring(Storage::disk('public')->get(Berita::first()->gambar));

        expect($lebar)->toBe(400);
    });

    it('mengganti gambar dan membuang berkas lama', function () {
        $admin = akun(Role::PetugasTu);
        $this->actingAs($admin)->post(route('admin.berita.store'), isianBerita(['gambar' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        $berita = Berita::first();
        $lama = $berita->gambar;

        $this->actingAs($admin)->put(route('admin.berita.update', $berita), isianBerita(['gambar' => UploadedFile::fake()->image('b.jpg', 600, 400)]));

        $baru = $berita->fresh()->gambar;

        expect($baru)->not->toBe($lama);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($baru);
    });

    it('mempertahankan gambar bila tidak ada unggahan baru, dan menghapusnya bila diminta', function () {
        $admin = akun(Role::PetugasTu);
        $this->actingAs($admin)->post(route('admin.berita.store'), isianBerita(['gambar' => UploadedFile::fake()->image('a.jpg', 600, 400)]));
        $berita = Berita::first();
        $path = $berita->gambar;

        $this->actingAs($admin)->put(route('admin.berita.update', $berita), isianBerita(['judul' => 'Judul Diubah']));
        expect($berita->fresh()->gambar)->toBe($path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($admin)->put(route('admin.berita.update', $berita), isianBerita(['hapus_gambar' => '1']));
        expect($berita->fresh()->gambar)->toBeNull();
        Storage::disk('public')->assertMissing($path);
    });

    it('menolak berkas yang bukan gambar atau terlalu besar', function (UploadedFile $berkas) {
        $this->actingAs(akun(Role::PetugasTu))
            ->post(route('admin.berita.store'), isianBerita(['gambar' => $berkas]))
            ->assertSessionHasErrors('gambar');

        expect(Berita::count())->toBe(0)->and(Storage::disk('public')->allFiles())->toBe([]);
    })->with([
        'dokumen teks' => fn () => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'),
        'php menyamar' => fn () => UploadedFile::fake()->create('shell.php', 10, 'image/jpeg'),
        'di atas 4 MB' => fn () => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(5000),
        'gif' => fn () => UploadedFile::fake()->image('anim.gif', 100, 100),
    ]);
});

describe('situs publik', function () {
    it('hanya menampilkan berita yang sudah tayang, terbaru lebih dulu', function () {
        Berita::factory()->create(['judul' => 'Berita Lama', 'tanggal_terbit' => now()->subDays(5)]);
        Berita::factory()->create(['judul' => 'Berita Baru', 'tanggal_terbit' => now()->subHour()]);
        Berita::factory()->draf()->create(['judul' => 'Berita Draf']);
        Berita::factory()->terjadwal()->create(['judul' => 'Berita Terjadwal']);
        Berita::factory()->create(['judul' => 'Berita Dihapus'])->delete();

        $this->get(route('berita.index'))
            ->assertOk()
            ->assertSeeInOrder(['Berita Baru', 'Berita Lama'])
            ->assertDontSee('Berita Draf')
            ->assertDontSee('Berita Terjadwal')
            ->assertDontSee('Berita Dihapus');
    });

    it('menyaring menurut jenis dan mengabaikan jenis yang tidak dikenal', function () {
        Berita::factory()->create(['judul' => 'Kabar Biasa']);
        Berita::factory()->pengumuman()->create(['judul' => 'Kabar Pengumuman']);

        $this->get(route('berita.index', ['jenis' => 'pengumuman']))->assertSee('Kabar Pengumuman')->assertDontSee('Kabar Biasa');
        $this->get(route('berita.index', ['jenis' => 'ngawur']))->assertOk()->assertSee('Kabar Biasa')->assertSee('Kabar Pengumuman');
    });

    it('membagi daftar per 9 berita', function () {
        Berita::factory()->count(11)->create();

        $this->get(route('berita.index'))->assertOk()->assertSee('page=2');
        expect(substr_count($this->get(route('berita.index'))->getContent(), '<article class="card'))->toBe(9);
    });

    it('menampilkan detail berita per paragraf dengan isi yang di-escape', function () {
        $berita = Berita::factory()->create(['judul' => 'Detail Uji', 'isi' => "Baris <b>satu</b>\nlanjutan\n\n<script>alert(1)</script>"]);

        $this->get(route('berita.show', $berita->slug))
            ->assertOk()
            ->assertSee('Detail Uji')
            ->assertSee('Baris &lt;b&gt;satu&lt;/b&gt;<br />', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    });

    it('mengembalikan 404 untuk draf, terjadwal, dihapus, atau slug yang tidak ada', function () {
        $draf = Berita::factory()->draf()->create();
        $dihapus = tap(Berita::factory()->create())->delete();

        foreach ([$draf->slug, Berita::factory()->terjadwal()->create()->slug, $dihapus->slug, 'tidak-ada'] as $slug) {
            $this->get(route('berita.show', $slug))->assertNotFound();
        }
    });

    it('menampilkan berita terbaru di beranda dan menyembunyikan bagiannya bila kosong', function () {
        $this->get(route('home'))->assertOk()->assertDontSee('Berita &amp; Pengumuman', false);

        Berita::factory()->create(['judul' => 'Kabar Beranda']);
        Berita::factory()->draf()->create(['judul' => 'Draf Tersembunyi']);
        CacheBeranda::lupakan();

        $this->get(route('home'))->assertSee('Kabar Beranda')->assertDontSee('Draf Tersembunyi');
    });

    it('memperbarui beranda segera setelah berita disimpan lewat panel admin', function () {
        $admin = akun(Role::PetugasTu);
        $this->get(route('home')); // mengisi cache kosong

        $this->actingAs($admin)->post(route('admin.berita.store'), isianBerita(['judul' => 'Berita Segar']));

        $this->get(route('home'))->assertSee('Berita Segar');

        $this->actingAs($admin)->delete(route('admin.berita.destroy', Berita::first()));

        $this->get(route('home'))->assertDontSee('Berita Segar');
    });

    it('menampilkan menu publik Berita dan tautan Lihat di admin hanya untuk yang tayang', function () {
        $this->get(route('home'))->assertSee(route('berita.index'));

        $tayang = Berita::factory()->create();
        $draf = Berita::factory()->draf()->create();

        $halaman = $this->actingAs(akun(Role::PetugasTu))->get(route('admin.berita.index'))->getContent();

        expect($halaman)->toContain(route('berita.show', $tayang->slug))->not->toContain(route('berita.show', $draf->slug));
    });
});
