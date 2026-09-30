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

beforeEach(function () {
    $this->smp = UnitSekolah::create(['nama_unit_sekolah' => 'SMP', 'jenjang' => Jenjang::Smp]);
    $this->smk = UnitSekolah::create(['nama_unit_sekolah' => 'SMK', 'jenjang' => Jenjang::Smk]);
    $tahun = TahunAjaran::create(['nama_tahun_ajaran' => '2026/2027', 'semester_aktif' => Semester::Ganjil, 'is_aktif' => true]);

    foreach ([$this->smp, $this->smk] as $unit) {
        Kelas::create(['id_unit_sekolah' => $unit->id_unit_sekolah, 'id_tahun_ajaran' => $tahun->id_tahun_ajaran, 'tingkat' => 7, 'nama_kelas' => 'Kelas '.$unit->jenjang->label()]);
        Jurusan::create(['id_unit_sekolah' => $unit->id_unit_sekolah, 'nama_jurusan' => 'Jurusan '.$unit->jenjang->label(), 'kode_jurusan' => strtoupper($unit->jenjang->value)]);
        Pegawai::create(['id_unit_sekolah' => $unit->id_unit_sekolah, 'nama_pegawai' => 'Guru '.$unit->jenjang->label(), 'jenis_pegawai' => JenisPegawai::Guru]);
    }

    $this->yayasan = Pegawai::create(['nama_pegawai' => 'Ketua Yayasan', 'jenis_pegawai' => JenisPegawai::PimpinanYayasan]);
});

function akunDariPegawai(Pegawai $pegawai): User
{
    return User::factory()->create(['id_pegawai' => $pegawai->id_pegawai]);
}

it('hanya menampilkan data unit sendiri untuk pegawai tingkat unit', function () {
    $guruSmp = Pegawai::withoutGlobalScopes()->where('nama_pegawai', 'Guru SMP')->first();

    $this->actingAs(akunDariPegawai($guruSmp));

    expect(Kelas::pluck('nama_kelas')->all())->toBe(['Kelas SMP'])
        ->and(Jurusan::pluck('nama_jurusan')->all())->toBe(['Jurusan SMP'])
        ->and(Pegawai::pluck('nama_pegawai')->all())->toBe(['Guru SMP']);
});

it('menampilkan semua unit untuk pegawai tingkat yayasan', function () {
    $this->actingAs(akunDariPegawai($this->yayasan));

    expect(Kelas::count())->toBe(2)
        ->and(Jurusan::count())->toBe(2)
        ->and(Pegawai::count())->toBe(3);
});

it('menampilkan semua unit untuk akun tanpa pegawai (super_admin) dan tanpa login (console)', function () {
    expect(Kelas::count())->toBe(2);

    $this->actingAs(User::factory()->create());

    expect(Kelas::count())->toBe(2)
        ->and(Pegawai::count())->toBe(3);
});

it('tetap bisa memuat relasi pegawai milik user tanpa rekursi scope', function () {
    $guruSmk = Pegawai::withoutGlobalScopes()->where('nama_pegawai', 'Guru SMK')->first();
    $user = akunDariPegawai($guruSmk);

    $this->actingAs($user);

    expect($user->fresh()->pegawai->nama_pegawai)->toBe('Guru SMK')
        ->and($user->fresh()->idUnitSekolah())->toBe($this->smk->id_unit_sekolah);
});

it('tidak menyaring saat query memakai kolom yang ambigu pada join', function () {
    $guruSmp = Pegawai::withoutGlobalScopes()->where('nama_pegawai', 'Guru SMP')->first();
    $this->actingAs(akunDariPegawai($guruSmp));

    $hasil = Kelas::query()->join('tb_jurusan', 'tb_jurusan.id_unit_sekolah', '=', 'tb_kelas.id_unit_sekolah')->count();

    expect($hasil)->toBe(1);
});
