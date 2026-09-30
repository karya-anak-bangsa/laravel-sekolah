<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;

beforeEach(function () {
    $this->withoutVite();
    $this->smp = buatUnit(Jenjang::Smp);
    $this->smk = buatUnit(Jenjang::Smk);
    $this->rpl = $this->smk->jurusan()->create(['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => 'RPL']);
    $this->tahun = buatTahunAjaran();
    $this->tuSmp = akun(Role::PetugasTu, $this->smp);
});

function dataKelasSmp(array $timpa = []): array
{
    return array_merge([
        'id_unit_sekolah' => test()->smp->id_unit_sekolah,
        'id_tahun_ajaran' => test()->tahun->id_tahun_ajaran,
        'tingkat' => 7,
        'nama_kelas' => 'VII-A',
    ], $timpa);
}

function dataKelasSmk(array $timpa = []): array
{
    return array_merge([
        'id_unit_sekolah' => test()->smk->id_unit_sekolah,
        'id_jurusan' => test()->rpl->id_jurusan,
        'id_tahun_ajaran' => test()->tahun->id_tahun_ajaran,
        'tingkat' => 10,
        'nama_kelas' => 'X RPL 1',
    ], $timpa);
}

function kelasSmk(string $nama = 'X RPL 1'): Kelas
{
    return Kelas::create(dataKelasSmk(['nama_kelas' => $nama]));
}

it('membuat kelas SMP tanpa jurusan untuk petugas TU unit SMP', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.kelas.store'), dataKelasSmp())
        ->assertRedirect(route('admin.kelas.index'));

    $kelas = Kelas::firstOrFail();
    expect($kelas->nama_kelas)->toBe('VII-A')->and($kelas->id_jurusan)->toBeNull();
});

it('membuat kelas SMK dengan jurusan dan wali kelas guru unit yang sama', function () {
    $guru = Pegawai::create(['id_unit_sekolah' => $this->smk->id_unit_sekolah, 'nama_pegawai' => 'Bu Guru', 'jenis_pegawai' => JenisPegawai::Guru]);

    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.kelas.store'), dataKelasSmk(['id_pegawai_wali_kelas' => $guru->id_pegawai]))
        ->assertSessionHasNoErrors();

    expect(Kelas::firstOrFail()->waliKelas->is($guru))->toBeTrue();
});

it('mewajibkan jurusan untuk SMK dan melarangnya untuk SMP', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.kelas.store'), dataKelasSmk(['id_jurusan' => null]))
        ->assertSessionHasErrors(['id_jurusan' => 'Jurusan wajib dipilih untuk kelas SMK.']);

    $this->post(route('admin.kelas.store'), dataKelasSmp(['id_jurusan' => $this->rpl->id_jurusan]))
        ->assertSessionHasErrors('id_jurusan');
});

it('menolak jurusan milik unit lain', function () {
    $smkLain = buatUnit(Jenjang::Smk);
    $jurusanLain = $smkLain->jurusan()->create(['nama_jurusan' => 'TKJ', 'kode_jurusan' => 'TKJ']);

    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.kelas.store'), dataKelasSmk(['id_jurusan' => $jurusanLain->id_jurusan]))
        ->assertSessionHasErrors('id_jurusan');
});

it('menolak tingkat yang tidak sesuai jenjang', function () {
    $this->actingAs(akun(Role::SuperAdmin))
        ->post(route('admin.kelas.store'), dataKelasSmp(['tingkat' => 10]))
        ->assertSessionHasErrors('tingkat');

    $this->post(route('admin.kelas.store'), dataKelasSmk(['tingkat' => 8]))
        ->assertSessionHasErrors('tingkat');
});

it('menolak wali kelas yang bukan guru atau beda unit', function () {
    $tu = Pegawai::create(['id_unit_sekolah' => $this->smk->id_unit_sekolah, 'nama_pegawai' => 'Pak TU', 'jenis_pegawai' => JenisPegawai::Tu]);
    $guruSmp = Pegawai::create(['id_unit_sekolah' => $this->smp->id_unit_sekolah, 'nama_pegawai' => 'Guru SMP', 'jenis_pegawai' => JenisPegawai::Guru]);
    $admin = akun(Role::SuperAdmin);

    $this->actingAs($admin)
        ->post(route('admin.kelas.store'), dataKelasSmk(['id_pegawai_wali_kelas' => $tu->id_pegawai]))
        ->assertSessionHasErrors('id_pegawai_wali_kelas');

    $this->post(route('admin.kelas.store'), dataKelasSmk(['id_pegawai_wali_kelas' => $guruSmp->id_pegawai]))
        ->assertSessionHasErrors('id_pegawai_wali_kelas');
});

it('menolak nama kelas ganda pada unit dan tahun ajaran yang sama, tetapi mengizinkan di tahun lain', function () {
    kelasSmk('X RPL 1');
    $admin = akun(Role::SuperAdmin);

    $this->actingAs($admin)
        ->post(route('admin.kelas.store'), dataKelasSmk())
        ->assertSessionHasErrors('nama_kelas');

    $tahunLain = buatTahunAjaran('2027/2028', false);
    $this->post(route('admin.kelas.store'), dataKelasSmk(['id_tahun_ajaran' => $tahunLain->id_tahun_ajaran]))
        ->assertSessionHasNoErrors();
});

it('membatasi petugas TU unit SMP: tidak bisa membuat kelas di unit SMK', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.kelas.store'), dataKelasSmk())
        ->assertSessionHasErrors('id_unit_sekolah');

    expect(Kelas::count())->toBe(0);
});

it('membatasi petugas TU unit SMP: tidak melihat atau mengubah kelas unit SMK', function () {
    $smkKelas = kelasSmk();
    Kelas::create(dataKelasSmp());

    $this->actingAs($this->tuSmp)
        ->get(route('admin.kelas.index'))
        ->assertOk()
        ->assertSee('VII-A')
        ->assertDontSee('X RPL 1');

    $this->get(route('admin.kelas.edit', $smkKelas))->assertNotFound();
    $this->put(route('admin.kelas.update', $smkKelas), dataKelasSmk(['nama_kelas' => 'Diubah']))->assertNotFound();
    $this->delete(route('admin.kelas.destroy', $smkKelas))->assertNotFound();

    expect($smkKelas->fresh()->nama_kelas)->toBe('X RPL 1');
});

it('menampilkan semua unit untuk super_admin dan memfilter tahun ajaran aktif secara bawaan', function () {
    $tahunLama = buatTahunAjaran('2025/2026', false);
    kelasSmk('X RPL 1');
    Kelas::create(dataKelasSmp());
    Kelas::create(dataKelasSmp(['id_tahun_ajaran' => $tahunLama->id_tahun_ajaran, 'nama_kelas' => 'VII-LAMA']));

    $this->actingAs(akun(Role::SuperAdmin));

    $this->get(route('admin.kelas.index'))->assertSee('X RPL 1')->assertSee('VII-A')->assertDontSee('VII-LAMA');
    $this->get(route('admin.kelas.index', ['id_tahun_ajaran' => '']))->assertSee('VII-LAMA');
    $this->get(route('admin.kelas.index', ['id_unit_sekolah' => $this->smk->id_unit_sekolah]))->assertSee('X RPL 1')->assertDontSee('VII-A');
    $this->get(route('admin.kelas.index', ['tingkat' => 7]))->assertSee('VII-A')->assertDontSee('X RPL 1');
});

it('memaginasi daftar kelas 25 per halaman', function () {
    foreach (range(1, 30) as $i) {
        Kelas::create(dataKelasSmp(['nama_kelas' => sprintf('VII-%02d', $i)]));
    }

    $this->actingAs(akun(Role::SuperAdmin))
        ->get(route('admin.kelas.index'))
        ->assertSee('VII-25')
        ->assertDontSee('VII-26')
        ->assertSee('dari 30 data');
});

it('mengizinkan kepala sekolah melihat tetapi tidak mengubah kelas', function () {
    $kelas = Kelas::create(dataKelasSmp());
    $kepsek = akun(Role::KepalaSekolah, $this->smp, JenisPegawai::Guru);

    $this->actingAs($kepsek)->get(route('admin.kelas.index'))->assertOk()->assertSee('VII-A');
    $this->get(route('admin.kelas.create'))->assertForbidden();
    $this->post(route('admin.kelas.store'), dataKelasSmp(['nama_kelas' => 'VII-B']))->assertForbidden();
    $this->put(route('admin.kelas.update', $kelas), dataKelasSmp(['nama_kelas' => 'X']))->assertForbidden();
    $this->delete(route('admin.kelas.destroy', $kelas))->assertForbidden();
});

it('menolak role tanpa kelas.view', function (Role $role) {
    $this->actingAs(akun($role, $this->smp))->get(route('admin.kelas.index'))->assertForbidden();
})->with([Role::WaliKelas, Role::GuruMapel, Role::GuruPiket]);

it('mengubah dan menghapus kelas oleh petugas TU unit yang sama', function () {
    $kelas = Kelas::create(dataKelasSmp());

    $this->actingAs($this->tuSmp)
        ->put(route('admin.kelas.update', $kelas), dataKelasSmp(['nama_kelas' => 'VII-B']))
        ->assertSessionHasNoErrors();
    expect($kelas->fresh()->nama_kelas)->toBe('VII-B');

    $this->delete(route('admin.kelas.destroy', $kelas))->assertRedirect(route('admin.kelas.index'));
    expect(Kelas::count())->toBe(0);
});
