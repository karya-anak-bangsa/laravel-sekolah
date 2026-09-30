<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;

beforeEach(function () {
    $this->withoutVite();
    $this->smp = buatUnit(Jenjang::Smp);
    $this->smk = buatUnit(Jenjang::Smk);
    $this->tuSmp = akun(Role::PetugasTu, $this->smp);
});

function pegawaiBaru(string $nama, ?int $idUnit, JenisPegawai $jenis = JenisPegawai::Guru): Pegawai
{
    return Pegawai::create(['nama_pegawai' => $nama, 'id_unit_sekolah' => $idUnit, 'jenis_pegawai' => $jenis]);
}

it('menampilkan hanya pegawai unit sendiri untuk petugas TU unit', function () {
    pegawaiBaru('Guru Satu SMP', $this->smp->id_unit_sekolah);
    pegawaiBaru('Guru Dua SMK', $this->smk->id_unit_sekolah);
    pegawaiBaru('Ketua Yayasan', null, JenisPegawai::PimpinanYayasan);

    $this->actingAs($this->tuSmp)
        ->get(route('admin.pegawai.index'))
        ->assertOk()
        ->assertSee('Guru Satu SMP')
        ->assertDontSee('Guru Dua SMK')
        ->assertDontSee('Ketua Yayasan');
});

it('menampilkan semua pegawai untuk super_admin dan pemilik yayasan', function (Role $role) {
    pegawaiBaru('Guru Satu SMP', $this->smp->id_unit_sekolah);
    pegawaiBaru('Guru Dua SMK', $this->smk->id_unit_sekolah);

    $this->actingAs(akun($role))
        ->get(route('admin.pegawai.index'))
        ->assertSee('Guru Satu SMP')
        ->assertSee('Guru Dua SMK');
})->with([Role::SuperAdmin, Role::PemilikYayasan]);

it('mencari dan memfilter pegawai', function () {
    pegawaiBaru('Budi Santoso', $this->smp->id_unit_sekolah);
    pegawaiBaru('Siti Aminah', $this->smp->id_unit_sekolah, JenisPegawai::Tu);

    $this->actingAs($this->tuSmp);

    $this->get(route('admin.pegawai.index', ['q' => 'budi']))->assertSee('Budi Santoso')->assertDontSee('Siti Aminah');
    $this->get(route('admin.pegawai.index', ['jenis_pegawai' => 'tu']))->assertSee('Siti Aminah')->assertDontSee('Budi Santoso');
});

it('memaginasi 25 pegawai per halaman', function () {
    foreach (range(1, 30) as $i) {
        pegawaiBaru(sprintf('Guru %02d', $i), $this->smp->id_unit_sekolah);
    }

    $this->actingAs(akun(Role::SuperAdmin))
        ->get(route('admin.pegawai.index'))
        ->assertSee('Guru 25')
        ->assertDontSee('Guru 26');
});

it('menambah pegawai oleh petugas TU di unitnya', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.pegawai.store'), ['nama_pegawai' => 'Guru Baru', 'jenis_pegawai' => 'guru', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])
        ->assertRedirect(route('admin.pegawai.index'));

    expect(Pegawai::where('nama_pegawai', 'Guru Baru')->exists())->toBeTrue();
});

it('menolak petugas TU unit SMP menambah pegawai di unit SMK atau pimpinan yayasan', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.pegawai.store'), ['nama_pegawai' => 'X', 'jenis_pegawai' => 'guru', 'id_unit_sekolah' => $this->smk->id_unit_sekolah])
        ->assertSessionHasErrors('id_unit_sekolah');

    $this->post(route('admin.pegawai.store'), ['nama_pegawai' => 'Y', 'jenis_pegawai' => 'pimpinan_yayasan'])
        ->assertSessionHasErrors('jenis_pegawai');

    expect(Pegawai::whereIn('nama_pegawai', ['X', 'Y'])->count())->toBe(0);
});

it('mewajibkan unit kecuali untuk pimpinan yayasan, dan melarang unit pada pimpinan yayasan', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.pegawai.store'), ['nama_pegawai' => 'A', 'jenis_pegawai' => 'guru'])
        ->assertSessionHasErrors('id_unit_sekolah');

    $this->post(route('admin.pegawai.store'), ['nama_pegawai' => 'Ketua', 'jenis_pegawai' => 'pimpinan_yayasan', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])
        ->assertSessionHasErrors('id_unit_sekolah');

    $this->post(route('admin.pegawai.store'), ['nama_pegawai' => 'Ketua', 'jenis_pegawai' => 'pimpinan_yayasan'])
        ->assertSessionHasNoErrors();

    expect(Pegawai::firstWhere('nama_pegawai', 'Ketua')->id_unit_sekolah)->toBeNull();
});

it('memvalidasi nama dan jenis pegawai', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.pegawai.store'), ['nama_pegawai' => '', 'jenis_pegawai' => 'kepsek', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])
        ->assertSessionHasErrors(['nama_pegawai' => 'Nama pegawai wajib diisi.', 'jenis_pegawai']);
});

it('mengubah pegawai dan memindahkannya ke tingkat yayasan membersihkan unit', function () {
    $pegawai = pegawaiBaru('Pak Lama', $this->smp->id_unit_sekolah);

    $this->actingAs(akun(Role::SuperAdmin))
        ->put(route('admin.pegawai.update', $pegawai), ['nama_pegawai' => 'Pak Baru', 'jenis_pegawai' => 'pimpinan_yayasan'])
        ->assertSessionHasNoErrors();

    expect($pegawai->fresh())->nama_pegawai->toBe('Pak Baru')->id_unit_sekolah->toBeNull();
});

it('tidak mengizinkan petugas TU mengubah atau menghapus pegawai unit lain', function () {
    $lain = pegawaiBaru('Guru SMK', $this->smk->id_unit_sekolah);

    $this->actingAs($this->tuSmp);
    $this->get(route('admin.pegawai.edit', $lain))->assertNotFound();
    $this->put(route('admin.pegawai.update', $lain), ['nama_pegawai' => 'Diubah', 'jenis_pegawai' => 'guru', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])->assertNotFound();
    $this->delete(route('admin.pegawai.destroy', $lain))->assertNotFound();

    expect($lain->fresh()->nama_pegawai)->toBe('Guru SMK');
});

it('menolak mengubah jenis guru yang masih menjadi wali kelas', function () {
    $guru = pegawaiBaru('Bu Wali', $this->smp->id_unit_sekolah);
    Kelas::create(['id_unit_sekolah' => $this->smp->id_unit_sekolah, 'id_tahun_ajaran' => buatTahunAjaran()->id_tahun_ajaran, 'tingkat' => 7, 'nama_kelas' => 'VII-A', 'id_pegawai_wali_kelas' => $guru->id_pegawai]);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.pegawai.update', $guru), ['nama_pegawai' => 'Bu Wali', 'jenis_pegawai' => 'tu', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])
        ->assertSessionHasErrors('jenis_pegawai');
});

it('menghapus pegawai beserta menonaktifkan akunnya', function () {
    $pegawai = pegawaiBaru('Pak Keluar', $this->smp->id_unit_sekolah);
    $akunPegawai = User::factory()->create(['id_pegawai' => $pegawai->id_pegawai]);

    $this->actingAs($this->tuSmp)->delete(route('admin.pegawai.destroy', $pegawai))->assertSessionHas('status');

    expect(Pegawai::find($pegawai->id_pegawai))->toBeNull()
        ->and(User::withTrashed()->find($akunPegawai->id_user)->trashed())->toBeTrue();
});

it('menolak menghapus pegawai yang masih menjadi wali kelas', function () {
    $guru = pegawaiBaru('Bu Wali', $this->smp->id_unit_sekolah);
    Kelas::create(['id_unit_sekolah' => $this->smp->id_unit_sekolah, 'id_tahun_ajaran' => buatTahunAjaran()->id_tahun_ajaran, 'tingkat' => 7, 'nama_kelas' => 'VII-A', 'id_pegawai_wali_kelas' => $guru->id_pegawai]);

    $this->actingAs($this->tuSmp)->from(route('admin.pegawai.index'))
        ->delete(route('admin.pegawai.destroy', $guru))
        ->assertSessionHas('error');

    expect(Pegawai::find($guru->id_pegawai))->not->toBeNull();
});

it('hanya mengizinkan lihat untuk pimpinan dan menolak role tanpa pegawai.view', function () {
    $pegawai = pegawaiBaru('Guru', $this->smp->id_unit_sekolah);

    $kepsek = akun(Role::KepalaSekolah, $this->smp, JenisPegawai::Guru);
    $this->actingAs($kepsek)->get(route('admin.pegawai.index'))->assertOk();
    $this->get(route('admin.pegawai.create'))->assertForbidden();
    $this->put(route('admin.pegawai.update', $pegawai), ['nama_pegawai' => 'X', 'jenis_pegawai' => 'guru', 'id_unit_sekolah' => $this->smp->id_unit_sekolah])->assertForbidden();
    $this->delete(route('admin.pegawai.destroy', $pegawai))->assertForbidden();

    foreach ([Role::WaliKelas, Role::GuruMapel, Role::GuruPiket] as $role) {
        $this->actingAs(akun($role, $this->smp))->get(route('admin.pegawai.index'))->assertForbidden();
    }
});

it('menolak tamu dan pendaftar', function () {
    $this->get(route('admin.pegawai.index'))->assertRedirect(route('admin.login'));
    $this->actingAs(akun(Role::Pendaftar))->get(route('admin.pegawai.index'))->assertForbidden();
});
