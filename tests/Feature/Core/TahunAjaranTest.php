<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\TahunAjaran;

beforeEach(function () {
    $this->withoutVite();
});

it('menampilkan daftar tahun ajaran dengan statusnya', function () {
    buatTahunAjaran('2026/2027', true);
    buatTahunAjaran('2025/2026', false);

    $this->actingAs(akun(Role::PetugasTu))
        ->get(route('admin.tahun-ajaran.index'))
        ->assertOk()
        ->assertSeeInOrder(['2026/2027', 'Aktif', '2025/2026', 'Tidak aktif']);
});

it('mengizinkan petugas TU dan super_admin mengelola, menolak role lain', function () {
    foreach ([Role::PetugasTu, Role::SuperAdmin] as $role) {
        $this->actingAs(akun($role))->get(route('admin.tahun-ajaran.create'))->assertOk();
    }

    foreach ([Role::KepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel] as $role) {
        $this->actingAs(akun($role))->get(route('admin.tahun-ajaran.index'))->assertForbidden();
    }
});

it('menambah tahun ajaran dalam keadaan tidak aktif', function () {
    $this->actingAs(akun(Role::PetugasTu))
        ->post(route('admin.tahun-ajaran.store'), ['nama_tahun_ajaran' => '2027/2028', 'semester_aktif' => 'ganjil'])
        ->assertRedirect(route('admin.tahun-ajaran.index'));

    expect(TahunAjaran::firstWhere('nama_tahun_ajaran', '2027/2028')->is_aktif)->toBeFalse();
});

it('memvalidasi format dan urutan tahun ajaran', function (string $input, bool $valid) {
    $response = $this->actingAs(akun(Role::PetugasTu))
        ->post(route('admin.tahun-ajaran.store'), ['nama_tahun_ajaran' => $input, 'semester_aktif' => 'genap']);

    $valid ? $response->assertSessionHasNoErrors() : $response->assertSessionHasErrors('nama_tahun_ajaran');
})->with([
    ['2026/2027', true],
    ['2026-2027', false],
    ['2026/2028', false],
    ['2027/2026', false],
    ['26/27', false],
    ['', false],
]);

it('menolak tahun ajaran ganda dan semester tidak dikenal', function () {
    buatTahunAjaran('2026/2027');

    $this->actingAs(akun(Role::PetugasTu))
        ->post(route('admin.tahun-ajaran.store'), ['nama_tahun_ajaran' => '2026/2027', 'semester_aktif' => 'ketiga'])
        ->assertSessionHasErrors(['nama_tahun_ajaran' => 'Tahun ajaran ini sudah terdaftar.', 'semester_aktif']);
});

it('mengubah semester tahun ajaran aktif tanpa bentrok dengan namanya sendiri', function () {
    $ta = buatTahunAjaran('2026/2027');

    $this->actingAs(akun(Role::PetugasTu))
        ->put(route('admin.tahun-ajaran.update', $ta), ['nama_tahun_ajaran' => '2026/2027', 'semester_aktif' => 'genap'])
        ->assertSessionHasNoErrors();

    expect($ta->fresh()->semester_aktif->value)->toBe('genap');
});

it('hanya menyisakan satu tahun ajaran aktif setelah Aktifkan', function () {
    $lama = buatTahunAjaran('2026/2027', true);
    $baru = buatTahunAjaran('2027/2028', false);

    $this->actingAs(akun(Role::PetugasTu))
        ->post(route('admin.tahun-ajaran.aktifkan', $baru))
        ->assertRedirect(route('admin.tahun-ajaran.index'));

    expect($baru->fresh()->is_aktif)->toBeTrue()
        ->and($lama->fresh()->is_aktif)->toBeFalse()
        ->and(TahunAjaran::aktif()->count())->toBe(1);
});

it('menolak mengaktifkan oleh role tanpa izin', function () {
    $ta = buatTahunAjaran('2026/2027', false);

    $this->actingAs(akun(Role::KepalaSekolah))
        ->post(route('admin.tahun-ajaran.aktifkan', $ta))
        ->assertForbidden();

    expect($ta->fresh()->is_aktif)->toBeFalse();
});

it('menolak menghapus tahun ajaran aktif atau yang punya kelas, dan menghapus yang bebas', function () {
    $aktif = buatTahunAjaran('2026/2027', true);
    $berkelas = buatTahunAjaran('2025/2026', false);
    $bebas = buatTahunAjaran('2024/2025', false);
    $smp = buatUnit(Jenjang::Smp);
    Kelas::create(['id_unit_sekolah' => $smp->id_unit_sekolah, 'id_tahun_ajaran' => $berkelas->id_tahun_ajaran, 'tingkat' => 7, 'nama_kelas' => 'VII-A']);

    $this->actingAs(akun(Role::PetugasTu))->from(route('admin.tahun-ajaran.index'));

    $this->delete(route('admin.tahun-ajaran.destroy', $aktif))->assertSessionHas('error');
    $this->delete(route('admin.tahun-ajaran.destroy', $berkelas))->assertSessionHas('error');
    $this->delete(route('admin.tahun-ajaran.destroy', $bebas))->assertSessionHas('status');

    expect(TahunAjaran::count())->toBe(2);
});
