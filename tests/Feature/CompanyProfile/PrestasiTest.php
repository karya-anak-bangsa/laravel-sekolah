<?php

use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use App\Modules\CompanyProfile\Models\Prestasi;
use App\Modules\CompanyProfile\Support\CacheBeranda;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('public');
});

/** Isian form prestasi yang valid; $ubah menimpa sebagian. */
function isianPrestasi(array $ubah = []): array
{
    return array_merge([
        'judul' => 'Juara 1 Lomba Web Design',
        'nama_peraih' => 'Tim RPL',
        'tingkat' => 'provinsi',
        'tahun' => now()->year,
        'id_unit_sekolah' => '',
    ], $ubah);
}

describe('panel admin', function () {
    it('menambah prestasi tingkat yayasan dan tingkat unit', function () {
        $smk = buatUnit(Jenjang::Smk);
        $admin = akun(Role::SuperAdmin);

        $this->actingAs($admin)->post(route('admin.prestasi.store'), isianPrestasi())->assertRedirect(route('admin.prestasi.index'));
        $this->actingAs($admin)->post(route('admin.prestasi.store'), isianPrestasi(['judul' => 'Juara Futsal', 'id_unit_sekolah' => $smk->id_unit_sekolah]));

        expect(Prestasi::firstWhere('judul', 'Juara 1 Lomba Web Design'))->id_unit_sekolah->toBeNull()->tingkat->toBe(TingkatPrestasi::Provinsi)
            ->and(Prestasi::firstWhere('judul', 'Juara Futsal')->id_unit_sekolah)->toBe($smk->id_unit_sekolah);
    });

    it('menyimpan foto sebagai WebP terkompres dan mengganti atau menghapusnya', function () {
        $admin = akun(Role::SuperAdmin);
        $this->actingAs($admin)->post(route('admin.prestasi.store'), isianPrestasi(['gambar' => UploadedFile::fake()->image('piala.jpg', 3000, 2000)]));
        $prestasi = Prestasi::first();
        $lama = $prestasi->gambar;

        [$lebar, , $jenis] = getimagesizefromstring(Storage::disk('public')->get($lama));
        expect($lama)->toStartWith('prestasi/')->and($lebar)->toBe(1200)->and($jenis)->toBe(IMAGETYPE_WEBP);

        $this->actingAs($admin)->put(route('admin.prestasi.update', $prestasi), isianPrestasi(['gambar' => UploadedFile::fake()->image('b.png', 500, 500)]));
        expect($prestasi->fresh()->gambar)->not->toBe($lama);
        Storage::disk('public')->assertMissing($lama);

        $baru = $prestasi->fresh()->gambar;
        $this->actingAs($admin)->put(route('admin.prestasi.update', $prestasi), isianPrestasi(['judul' => 'Judul Diubah']));
        expect($prestasi->fresh())->gambar->toBe($baru)->judul->toBe('Judul Diubah');

        $this->actingAs($admin)->put(route('admin.prestasi.update', $prestasi), isianPrestasi(['hapus_gambar' => '1']));
        expect($prestasi->fresh()->gambar)->toBeNull();
        Storage::disk('public')->assertMissing($baru);
    });

    it('memvalidasi isian prestasi', function (array $ubah, string $kolom) {
        $this->actingAs(akun(Role::SuperAdmin))
            ->post(route('admin.prestasi.store'), isianPrestasi($ubah))
            ->assertSessionHasErrors($kolom);

        expect(Prestasi::count())->toBe(0);
    })->with([
        'judul wajib' => [['judul' => ''], 'judul'],
        'nama peraih wajib' => [['nama_peraih' => ''], 'nama_peraih'],
        'tingkat tidak dikenal' => [['tingkat' => 'dunia'], 'tingkat'],
        'tahun bukan angka' => [['tahun' => 'tahun ini'], 'tahun'],
        'tahun terlalu lama' => [['tahun' => 1990], 'tahun'],
        'tahun terlalu jauh' => [['tahun' => 2999], 'tahun'],
        'unit tidak ada' => [['id_unit_sekolah' => 9999], 'id_unit_sekolah'],
        'foto bukan gambar' => [['gambar' => UploadedFile::fake()->create('a.txt', 5, 'text/plain')], 'gambar'],
        'foto terlalu besar' => [['gambar' => UploadedFile::fake()->image('a.jpg')->size(5000)], 'gambar'],
    ]);

    it('menghapus prestasi secara soft delete', function () {
        $prestasi = Prestasi::factory()->create();

        $this->actingAs(akun(Role::SuperAdmin))->delete(route('admin.prestasi.destroy', $prestasi))->assertRedirect(route('admin.prestasi.index'));

        expect(Prestasi::find($prestasi->id_prestasi))->toBeNull()->and(Prestasi::withTrashed()->find($prestasi->id_prestasi))->not->toBeNull();
    });

    it('memfilter daftar menurut kata kunci, tingkat, dan tahun', function () {
        Prestasi::factory()->create(['judul' => 'Olimpiade Sains', 'nama_peraih' => 'Budi', 'tingkat' => 'nasional', 'tahun' => 2025]);
        Prestasi::factory()->create(['judul' => 'Lomba Futsal', 'nama_peraih' => 'Tim Putra', 'tingkat' => 'kota', 'tahun' => 2024]);

        $admin = akun(Role::SuperAdmin);

        $this->actingAs($admin)->get(route('admin.prestasi.index', ['q' => 'Budi']))->assertSee('Olimpiade Sains')->assertDontSee('Lomba Futsal');
        $this->actingAs($admin)->get(route('admin.prestasi.index', ['tingkat' => 'kota']))->assertSee('Lomba Futsal')->assertDontSee('Olimpiade Sains');
        $this->actingAs($admin)->get(route('admin.prestasi.index', ['tahun' => 2025]))->assertSee('Olimpiade Sains')->assertDontSee('Lomba Futsal');
    });

    it('hanya mengizinkan super_admin, menolak role lain dan tamu', function (Role $role) {
        $prestasi = Prestasi::factory()->create(['judul' => 'Tetap']);
        $user = akun($role);

        $this->actingAs($user)->get(route('admin.prestasi.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.prestasi.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.prestasi.store'), isianPrestasi())->assertForbidden();
        $this->actingAs($user)->put(route('admin.prestasi.update', $prestasi), isianPrestasi(['judul' => 'Dibajak']))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.prestasi.destroy', $prestasi))->assertForbidden();

        expect($prestasi->fresh()->judul)->toBe('Tetap')->and(Prestasi::count())->toBe(1);
    })->with([Role::PetugasTu, Role::KepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel]);

    it('menolak tamu', function () {
        $this->get(route('admin.prestasi.index'))->assertRedirect();
    });
});

describe('situs publik', function () {
    it('mengurutkan prestasi: tahun terbaru dulu, lalu tingkat tertinggi', function () {
        Prestasi::factory()->create(['judul' => 'P Kota 2025', 'tingkat' => 'kota', 'tahun' => 2025]);
        Prestasi::factory()->create(['judul' => 'P Nasional 2025', 'tingkat' => 'nasional', 'tahun' => 2025]);
        Prestasi::factory()->create(['judul' => 'P Nasional 2023', 'tingkat' => 'nasional', 'tahun' => 2023]);
        Prestasi::factory()->create(['judul' => 'P Dihapus', 'tahun' => 2026])->delete();

        $this->get(route('prestasi'))
            ->assertOk()
            ->assertSeeInOrder(['P Nasional 2025', 'P Kota 2025', 'P Nasional 2023'])
            ->assertDontSee('P Dihapus');
    });

    it('menyaring menurut tingkat dan mengabaikan tingkat yang tidak dikenal', function () {
        Prestasi::factory()->create(['judul' => 'Sekolah Saja', 'tingkat' => 'sekolah']);
        Prestasi::factory()->create(['judul' => 'Tingkat Provinsi', 'tingkat' => 'provinsi']);

        $this->get(route('prestasi', ['tingkat' => 'provinsi']))->assertSee('Tingkat Provinsi')->assertDontSee('Sekolah Saja');
        $this->get(route('prestasi', ['tingkat' => 'ngawur']))->assertOk()->assertSee('Tingkat Provinsi')->assertSee('Sekolah Saja');
    });

    it('menampilkan nama peraih, unit, dan pesan bila kosong', function () {
        $this->get(route('prestasi'))->assertOk()->assertSee('Belum ada prestasi');

        $smk = buatUnit(Jenjang::Smk);
        Prestasi::factory()->create(['judul' => 'Juara Robotik', 'nama_peraih' => 'Siti Aminah', 'id_unit_sekolah' => $smk->id_unit_sekolah]);

        $this->get(route('prestasi'))->assertSee('Juara Robotik')->assertSee('Siti Aminah')->assertSee('SMK');
    });

    it('membagi daftar per 12 prestasi', function () {
        Prestasi::factory()->count(14)->create();

        $halaman = $this->get(route('prestasi'))->assertOk()->assertSee('page=2', false)->getContent();

        expect(substr_count($halaman, '<article class="card'))->toBe(12);
    });

    it('meloloskan isi prestasi sebagai teks, bukan HTML', function () {
        Prestasi::factory()->create(['judul' => '<script>alert(1)</script>', 'nama_peraih' => '<img src=x onerror=alert(1)>']);

        $this->get(route('prestasi'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    });

    it('menampilkan 3 prestasi terbaru di beranda dan memperbaruinya setelah admin menambah prestasi', function () {
        $this->get(route('home'))->assertDontSee('id="prestasi"', false);

        Prestasi::factory()->count(5)->create();
        CacheBeranda::lupakan();

        expect(substr_count($this->get(route('home'))->getContent(), '<article class="card'))->toBe(3);

        $this->actingAs(akun(Role::SuperAdmin))->post(route('admin.prestasi.store'), isianPrestasi(['judul' => 'Prestasi Paling Baru', 'tahun' => now()->year + 1, 'tingkat' => 'nasional']));

        $this->get(route('home'))->assertSee('Prestasi Paling Baru');
    });

    it('menampilkan menu Galeri dan Prestasi di navigasi publik dan admin', function () {
        $this->get(route('home'))->assertSee(route('galeri'))->assertSee(route('prestasi'));

        $this->actingAs(akun(Role::SuperAdmin))->get(route('admin.dashboard'))->assertSee('Galeri Foto')->assertSee('Prestasi');
        $this->actingAs(akun(Role::PetugasTu))->get(route('admin.dashboard'))->assertDontSee('Galeri Foto');
    });
});
