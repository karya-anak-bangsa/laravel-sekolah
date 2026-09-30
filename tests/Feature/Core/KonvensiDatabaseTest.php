<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Semester;
use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\TahunAjaran;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

it('memakai prefix tb_ dan primary key id_<nama> pada tabel domain', function (string $tabel, string $pk) {
    expect(Schema::hasTable($tabel))->toBeTrue()
        ->and(Schema::hasColumn($tabel, $pk))->toBeTrue()
        ->and(Schema::hasColumn($tabel, 'id'))->toBeFalse();
})->with([
    ['tb_unit_sekolah', 'id_unit_sekolah'],
    ['tb_jurusan', 'id_jurusan'],
    ['tb_tahun_ajaran', 'id_tahun_ajaran'],
    ['tb_pegawai', 'id_pegawai'],
    ['tb_kelas', 'id_kelas'],
    ['tb_user', 'id_user'],
]);

it('tidak memakai tabel users bawaan Laravel', function () {
    expect(Schema::hasTable('users'))->toBeFalse();
});

it('model membaca tabel dan primary key yang benar', function (string $model, string $tabel, string $pk) {
    $instance = new $model;

    expect($instance->getTable())->toBe($tabel)
        ->and($instance->getKeyName())->toBe($pk);
})->with([
    [UnitSekolah::class, 'tb_unit_sekolah', 'id_unit_sekolah'],
    [Jurusan::class, 'tb_jurusan', 'id_jurusan'],
    [TahunAjaran::class, 'tb_tahun_ajaran', 'id_tahun_ajaran'],
    [Pegawai::class, 'tb_pegawai', 'id_pegawai'],
    [Kelas::class, 'tb_kelas', 'id_kelas'],
    [User::class, 'tb_user', 'id_user'],
]);

it('menghubungkan kelas SMK dengan unit, jurusan, tahun ajaran, dan wali kelas', function () {
    $unit = UnitSekolah::create(['nama_unit_sekolah' => 'SMK Puspita Bangsa', 'jenjang' => Jenjang::Smk]);
    $jurusan = $unit->jurusan()->create(['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => 'RPL']);
    $tahun = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester_aktif' => Semester::Ganjil, 'is_aktif' => true]);
    $guru = Pegawai::create(['id_unit_sekolah' => $unit->id_unit_sekolah, 'nama_pegawai' => 'Budi', 'jenis_pegawai' => JenisPegawai::Guru]);

    $kelas = Kelas::create([
        'id_unit_sekolah' => $unit->id_unit_sekolah,
        'id_jurusan' => $jurusan->id_jurusan,
        'id_tahun_ajaran' => $tahun->id_tahun_ajaran,
        'tingkat' => 10,
        'nama_kelas' => 'X RPL 1',
        'id_pegawai_wali_kelas' => $guru->id_pegawai,
    ]);

    expect($kelas->unitSekolah->is($unit))->toBeTrue()
        ->and($kelas->jurusan->is($jurusan))->toBeTrue()
        ->and($kelas->tahunAjaran->is($tahun))->toBeTrue()
        ->and($kelas->waliKelas->is($guru))->toBeTrue()
        ->and($guru->kelasDiampu)->toHaveCount(1)
        ->and(TahunAjaran::aktif()->first()->is($tahun))->toBeTrue();
});

it('mengizinkan kelas SMP tanpa jurusan dan pegawai tingkat yayasan tanpa unit', function () {
    $unit = UnitSekolah::create(['nama_unit_sekolah' => 'SMP Puspita Bangsa', 'jenjang' => Jenjang::Smp]);
    $tahun = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester_aktif' => Semester::Ganjil, 'is_aktif' => true]);

    $kelas = Kelas::create([
        'id_unit_sekolah' => $unit->id_unit_sekolah,
        'id_tahun_ajaran' => $tahun->id_tahun_ajaran,
        'tingkat' => 7,
        'nama_kelas' => 'VII-A',
    ]);
    $pimpinan = Pegawai::create(['nama_pegawai' => 'Ketua Yayasan', 'jenis_pegawai' => JenisPegawai::PimpinanYayasan]);

    expect($kelas->jurusan)->toBeNull()
        ->and($kelas->waliKelas)->toBeNull()
        ->and($pimpinan->unitSekolah)->toBeNull();
});

it('membuat user pegawai dan pendaftar lewat factory dengan primary key id_user', function () {
    $pegawai = Pegawai::create(['nama_pegawai' => 'Sari', 'jenis_pegawai' => JenisPegawai::Tu]);
    $akunPegawai = User::factory()->create(['id_pegawai' => $pegawai->id_pegawai]);
    $akunPendaftar = User::factory()->pendaftar()->create();

    expect($akunPegawai->id_user)->toBeInt()
        ->and($akunPegawai->pegawai->is($pegawai))->toBeTrue()
        ->and($pegawai->user->is($akunPegawai))->toBeTrue()
        ->and($akunPendaftar->username)->toBeNull()
        ->and($akunPendaftar->no_hp)->toStartWith('08')
        ->and($akunPendaftar->id_pegawai)->toBeNull();
});

it('menyimpan role spatie untuk user ber-primary key id_user', function () {
    $user = User::factory()->create();
    Role::create(['name' => 'petugas_tu', 'guard_name' => 'web']);
    $user->assignRole('petugas_tu');

    expect($user->fresh()->hasRole('petugas_tu'))->toBeTrue();
});
