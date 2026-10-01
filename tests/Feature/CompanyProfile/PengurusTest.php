<?php

use App\Modules\CompanyProfile\Models\Pengurus;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;

beforeEach(function () {
    $this->withoutVite();
});

function buatPengurus(string $nama, string $jabatan = 'Anggota', int $urutan = 0, ?int $idUnit = null): Pengurus
{
    return Pengurus::create(['nama_pengurus' => $nama, 'jabatan' => $jabatan, 'urutan' => $urutan, 'id_unit_sekolah' => $idUnit]);
}

it('menampilkan daftar pengurus: yayasan dulu, lalu per unit menurut urutan', function () {
    $smp = buatUnit(Jenjang::Smp);
    buatPengurus('Kepala SMP', 'Kepala Sekolah', 1, $smp->id_unit_sekolah);
    buatPengurus('Bendahara', 'Bendahara Yayasan', 2);
    buatPengurus('Ketua', 'Ketua Yayasan', 1);

    $this->actingAs(akun(Role::SuperAdmin))
        ->get(route('admin.pengurus.index'))
        ->assertOk()
        ->assertSeeInOrder(['Ketua', 'Bendahara', 'Kepala SMP']);
});

it('menambah pengurus tingkat yayasan dan tingkat unit', function () {
    $smk = buatUnit(Jenjang::Smk);
    $admin = akun(Role::SuperAdmin);

    $this->actingAs($admin)
        ->post(route('admin.pengurus.store'), ['nama_pengurus' => 'Bu Ketua', 'jabatan' => 'Ketua Yayasan', 'id_unit_sekolah' => '', 'urutan' => ''])
        ->assertRedirect(route('admin.pengurus.index'));

    $this->actingAs($admin)
        ->post(route('admin.pengurus.store'), ['nama_pengurus' => 'Pak Kepala', 'jabatan' => 'Kepala SMK', 'id_unit_sekolah' => $smk->id_unit_sekolah, 'urutan' => 3])
        ->assertSessionHasNoErrors();

    expect(Pengurus::firstWhere('nama_pengurus', 'Bu Ketua'))->id_unit_sekolah->toBeNull()->urutan->toBe(0)
        ->and(Pengurus::firstWhere('nama_pengurus', 'Pak Kepala'))->id_unit_sekolah->toBe($smk->id_unit_sekolah)->urutan->toBe(3);
});

it('memvalidasi isian pengurus', function (array $data, string $kolom) {
    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.pengurus.store'), $data + ['nama_pengurus' => 'A', 'jabatan' => 'B'])
        ->assertSessionHasErrors($kolom);
})->with([
    'nama wajib' => [['nama_pengurus' => ''], 'nama_pengurus'],
    'jabatan wajib' => [['jabatan' => ''], 'jabatan'],
    'unit tidak ada' => [['id_unit_sekolah' => 9999], 'id_unit_sekolah'],
    'urutan bukan angka' => [['urutan' => 'satu'], 'urutan'],
    'urutan negatif' => [['urutan' => -1], 'urutan'],
]);

it('mengubah dan menghapus pengurus (soft delete)', function () {
    $pengurus = buatPengurus('Lama', 'Ketua');
    $admin = akun(Role::SuperAdmin);

    $this->actingAs($admin)
        ->put(route('admin.pengurus.update', $pengurus), ['nama_pengurus' => 'Baru', 'jabatan' => 'Ketua', 'urutan' => 5])
        ->assertRedirect(route('admin.pengurus.index'));

    expect($pengurus->fresh()->nama_pengurus)->toBe('Baru');

    $this->actingAs($admin)->delete(route('admin.pengurus.destroy', $pengurus))->assertRedirect(route('admin.pengurus.index'));

    expect(Pengurus::find($pengurus->id_pengurus))->toBeNull()
        ->and(Pengurus::withTrashed()->find($pengurus->id_pengurus))->not->toBeNull();
});

it('hanya mengizinkan super_admin mengelola pengurus, menolak role lain', function (Role $role) {
    $pengurus = buatPengurus('Tetap');
    $user = akun($role);

    $this->actingAs($user)->get(route('admin.pengurus.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.pengurus.create'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.pengurus.store'), ['nama_pengurus' => 'X', 'jabatan' => 'Y'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.pengurus.update', $pengurus), ['nama_pengurus' => 'X', 'jabatan' => 'Y'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.pengurus.destroy', $pengurus))->assertForbidden();

    expect($pengurus->fresh()->nama_pengurus)->toBe('Tetap');
})->with([Role::PetugasTu, Role::KepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel]);
