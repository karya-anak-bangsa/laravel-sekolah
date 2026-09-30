<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\UnitSekolah;

beforeEach(function () {
    $this->withoutVite();
    $this->smp = buatUnit(Jenjang::Smp);
    $this->smk = buatUnit(Jenjang::Smk);
    $this->admin = akun(Role::SuperAdmin);
});

it('menampilkan daftar unit untuk super_admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.unit-sekolah.index'))
        ->assertOk()
        ->assertSee('SMP Uji')
        ->assertSee('SMK Uji');
});

it('menolak role tanpa permission unit-sekolah.view', function (Role $role) {
    $this->actingAs(akun($role, $this->smp))
        ->get(route('admin.unit-sekolah.index'))
        ->assertForbidden();
})->with([Role::PetugasTu, Role::WaliKelas, Role::GuruMapel, Role::GuruPiket, Role::KepalaSekolah]);

it('menolak tamu dan pendaftar', function () {
    $this->get(route('admin.unit-sekolah.index'))->assertRedirect(route('admin.login'));

    $this->actingAs(akun(Role::Pendaftar))
        ->get(route('admin.unit-sekolah.index'))
        ->assertForbidden();
});

it('menambah dan mengubah unit sekolah', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.unit-sekolah.store'), ['nama_unit_sekolah' => 'SMK Baru', 'jenjang' => 'smk'])
        ->assertRedirect(route('admin.unit-sekolah.index'));

    $unit = UnitSekolah::where('nama_unit_sekolah', 'SMK Baru')->firstOrFail();

    $this->put(route('admin.unit-sekolah.update', $unit), ['nama_unit_sekolah' => 'SMK Puspita', 'jenjang' => 'smk'])
        ->assertRedirect(route('admin.unit-sekolah.index'));

    expect($unit->fresh()->nama_unit_sekolah)->toBe('SMK Puspita');
});

it('memvalidasi form unit sekolah', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.unit-sekolah.store'), ['nama_unit_sekolah' => '', 'jenjang' => 'sma'])
        ->assertSessionHasErrors(['nama_unit_sekolah' => 'Nama unit sekolah wajib diisi.', 'jenjang']);
});

it('menolak penghapusan unit yang masih punya jurusan, dan menghapus unit kosong', function () {
    $this->smk->jurusan()->create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);
    $kosong = UnitSekolah::create(['nama_unit_sekolah' => 'Unit Kosong', 'jenjang' => Jenjang::Smp]);

    $this->actingAs($this->admin)->from(route('admin.unit-sekolah.index'))
        ->delete(route('admin.unit-sekolah.destroy', $this->smk))
        ->assertRedirect(route('admin.unit-sekolah.index'))
        ->assertSessionHas('error');

    expect(UnitSekolah::find($this->smk->id_unit_sekolah))->not->toBeNull();

    $this->delete(route('admin.unit-sekolah.destroy', $kosong))->assertSessionHas('status');
    expect(UnitSekolah::find($kosong->id_unit_sekolah))->toBeNull();
});

it('menambahkan jurusan ke unit SMK dengan kode otomatis huruf besar', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.jurusan.store', $this->smk), ['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => ' rpl '])
        ->assertRedirect(route('admin.unit-sekolah.edit', $this->smk));

    expect(Jurusan::first())->kode_jurusan->toBe('RPL')->id_unit_sekolah->toBe($this->smk->id_unit_sekolah);
});

it('menolak kode jurusan ganda dalam satu unit', function () {
    $this->smk->jurusan()->create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);

    $this->actingAs($this->admin)
        ->post(route('admin.jurusan.store', $this->smk), ['nama_jurusan' => 'Lain', 'kode_jurusan' => 'rpl'])
        ->assertSessionHasErrors('kode_jurusan');
});

it('tidak memiliki jurusan untuk unit SMP', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.jurusan.create', $this->smp))
        ->assertNotFound();
});

it('mengubah jurusan tanpa bentrok dengan kodenya sendiri', function () {
    $jurusan = $this->smk->jurusan()->create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);

    $this->actingAs($this->admin)
        ->put(route('admin.jurusan.update', $jurusan), ['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => 'RPL'])
        ->assertSessionHasNoErrors();

    expect($jurusan->fresh()->nama_jurusan)->toBe('Rekayasa Perangkat Lunak');
});

it('menolak penghapusan jurusan yang dipakai kelas', function () {
    $jurusan = $this->smk->jurusan()->create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);
    Kelas::create(['id_unit_sekolah' => $this->smk->id_unit_sekolah, 'id_jurusan' => $jurusan->id_jurusan, 'id_tahun_ajaran' => buatTahunAjaran()->id_tahun_ajaran, 'tingkat' => 10, 'nama_kelas' => 'X RPL 1']);

    $this->actingAs($this->admin)->from(route('admin.unit-sekolah.edit', $this->smk))
        ->delete(route('admin.jurusan.destroy', $jurusan))
        ->assertSessionHas('error');

    expect(Jurusan::find($jurusan->id_jurusan))->not->toBeNull();
});

it('menampilkan halaman kelola unit SMK beserta jurusannya', function () {
    $this->smk->jurusan()->create(['nama_jurusan' => 'Pariwisata', 'kode_jurusan' => 'PAR']);

    $this->actingAs($this->admin)
        ->get(route('admin.unit-sekolah.edit', $this->smk))
        ->assertOk()
        ->assertSee('Pariwisata')
        ->assertSee('Tambah Jurusan');
});
