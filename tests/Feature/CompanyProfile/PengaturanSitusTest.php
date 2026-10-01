<?php

use App\Modules\CompanyProfile\Models\Pengaturan;
use App\Modules\CompanyProfile\Services\PengaturanSitus;
use App\Modules\CompanyProfile\Support\KatalogPengaturan;
use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

/** Isian form yang valid; $ubah menimpa sebagian kunci. */
function isianPengaturan(array $ubah = []): array
{
    return array_merge(
        collect(KatalogPengaturan::semua())->map(fn (array $definisi) => $definisi['bawaan'])->all(),
        $ubah,
    );
}

it('memakai nilai bawaan katalog selama belum ada yang disimpan', function () {
    expect(situs('nama'))->toBe(config('sekolah.nama'))
        ->and(situs('visi'))->toContain('generasi')
        ->and(Pengaturan::count())->toBe(0);
});

it('menampilkan formulir pengaturan berisi nilai saat ini untuk super_admin', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->get(route('admin.pengaturan.edit'))
        ->assertOk()
        ->assertSee('Identitas &amp; Beranda', false)
        ->assertSee('Kontak &amp; Lokasi', false)
        ->assertSee(config('sekolah.nama'));
});

it('menyimpan pengaturan dan langsung memakainya di situs publik', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->put(route('admin.pengaturan.update'), isianPengaturan([
            'nama' => 'Yayasan Uji Coba',
            'nama_singkat' => 'Uji Coba',
            'hero_judul' => 'Judul beranda baru',
            'telepon' => '0812-0000-1111',
        ]))
        ->assertRedirect(route('admin.pengaturan.edit'))
        ->assertSessionHas('status');

    expect(Pengaturan::firstWhere('kunci', 'nama')->nilai)->toBe('Yayasan Uji Coba');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Judul beranda baru')
        ->assertSee('0812-0000-1111')
        ->assertSee('Yayasan Uji Coba');
});

it('membersihkan cache saat pengaturan disimpan', function () {
    expect(situs('nama_singkat'))->toBe(config('sekolah.nama_singkat')); // mengisi cache

    app(PengaturanSitus::class)->simpan(['nama_singkat' => 'Baru']);

    expect(situs('nama_singkat'))->toBe('Baru');
});

it('mengosongkan isian opsional tanpa kembali ke nilai bawaan', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->put(route('admin.pengaturan.update'), isianPengaturan(['telepon' => '', 'jam_layanan' => '  ']))
        ->assertSessionHasNoErrors();

    expect(Pengaturan::firstWhere('kunci', 'telepon')->nilai)->toBeNull()
        ->and(situs('telepon'))->toBeNull()
        ->and(situs('jam_layanan'))->toBeNull();
});

it('mengabaikan kunci yang tidak ada di katalog', function () {
    app(PengaturanSitus::class)->simpan(['nama' => 'Sah', 'kunci_liar' => 'x']);

    expect(Pengaturan::where('kunci', 'kunci_liar')->exists())->toBeFalse();
});

it('memvalidasi isian pengaturan', function (array $ubah, string $kolom) {
    $this->actingAs(akun(Role::SuperAdmin))
        ->put(route('admin.pengaturan.update'), isianPengaturan($ubah))
        ->assertSessionHasErrors($kolom);

    expect(Pengaturan::count())->toBe(0);
})->with([
    'nama wajib' => [['nama' => ''], 'nama'],
    'nama singkat wajib' => [['nama_singkat' => ''], 'nama_singkat'],
    'email tidak valid' => [['email' => 'bukan-email'], 'email'],
    'peta bukan sematan Google Maps' => [['peta_embed_url' => 'https://contoh.com/peta'], 'peta_embed_url'],
    'peta tidak https' => [['peta_embed_url' => 'http://www.google.com/maps/embed?pb=1'], 'peta_embed_url'],
    'instagram bukan https' => [['instagram' => 'javascript:alert(1)'], 'instagram'],
    'sejarah terlalu panjang' => [['sejarah' => str_repeat('a', 5001)], 'sejarah'],
]);

it('menerima alamat sematan Google Maps dan menampilkannya di halaman kontak', function () {
    $peta = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1';

    $this->actingAs(akun(Role::SuperAdmin))
        ->put(route('admin.pengaturan.update'), isianPengaturan(['peta_embed_url' => $peta]))
        ->assertSessionHasNoErrors();

    $this->get(route('kontak'))->assertOk()->assertSee('<iframe', false)->assertSee(e($peta), false);
});

it('hanya mengizinkan super_admin mengubah pengaturan, menolak role lain', function (Role $role) {
    $this->actingAs(akun($role))->get(route('admin.pengaturan.edit'))->assertForbidden();
    $this->actingAs(akun($role))->put(route('admin.pengaturan.update'), isianPengaturan(['nama' => 'Dibajak']))->assertForbidden();

    expect(Pengaturan::count())->toBe(0);
})->with([Role::PetugasTu, Role::KepalaSekolah, Role::WakilKepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel, Role::GuruPiket]);

it('menolak tamu dan akun pendaftar dari pengaturan situs', function () {
    $this->get(route('admin.pengaturan.edit'))->assertRedirect();
    $this->put(route('admin.pengaturan.update'), isianPengaturan())->assertRedirect();

    app(RolePermissionSeeder::class)->run();
    $pendaftar = User::factory()->create(['no_hp' => '081234567890']);
    $pendaftar->assignRole(Role::Pendaftar->value);

    $this->actingAs($pendaftar)->get(route('admin.pengaturan.edit'))->assertForbidden();
});

it('menampilkan menu Pengaturan Situs hanya untuk pengguna yang berhak', function () {
    $this->actingAs(akun(Role::SuperAdmin))->get(route('admin.dashboard'))->assertSee('Pengaturan Situs');
    $this->actingAs(akun(Role::PetugasTu))->get(route('admin.dashboard'))->assertDontSee('Pengaturan Situs');
});
