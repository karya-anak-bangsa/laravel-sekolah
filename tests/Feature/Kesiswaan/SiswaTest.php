<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kesiswaan\Actions\SimpanDataSiswa;
use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use App\Modules\Kesiswaan\Models\Siswa;
use App\Modules\Kesiswaan\Models\WaliSiswa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutVite();
    $this->smp = buatUnit(Jenjang::Smp);
    $this->smk = buatUnit(Jenjang::Smk);
    $this->tahun = buatTahunAjaran();
    $this->tuSmp = akun(Role::PetugasTu, $this->smp);
});

function siswaDi($unit, array $atribut = []): Siswa
{
    return Siswa::factory()->create(['id_unit_sekolah' => $unit->id_unit_sekolah] + $atribut);
}

function kelasDi($unit, string $nama = 'VII-A', ?int $idTahun = null, ?int $idWali = null): Kelas
{
    return Kelas::create([
        'id_unit_sekolah' => $unit->id_unit_sekolah,
        'id_tahun_ajaran' => $idTahun ?? test()->tahun->id_tahun_ajaran,
        'tingkat' => $unit->jenjang === Jenjang::Smp ? 7 : 10,
        'nama_kelas' => $nama,
        'id_pegawai_wali_kelas' => $idWali,
        'id_jurusan' => $unit->jenjang === Jenjang::Smk ? $unit->jurusan()->firstOrCreate(['kode_jurusan' => 'RPL'], ['nama_jurusan' => 'RPL'])->id_jurusan : null,
    ]);
}

function payloadSiswa(array $timpa = []): array
{
    return array_replace_recursive([
        'status_siswa' => 'aktif',
        'nama_siswa' => 'Siswa Uji',
        'jenis_kelamin' => 'l',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '2012-05-10',
        'agama' => 'islam',
        'kebutuhan_khusus' => 'tidak_ada',
        'wali' => [
            'ayah' => ['nama_wali_siswa' => 'Ayah Uji'],
            'ibu' => ['nama_wali_siswa' => 'Ibu Uji'],
        ],
    ], $timpa);
}

// ---------------------------------------------------------------- skema

it('menjaga NISN unik di antara siswa aktif, tetapi bisa dipakai lagi setelah siswa lama di-soft delete', function () {
    $lama = siswaDi($this->smp, ['nisn' => '0012345678']);

    expect(fn () => siswaDi($this->smp, ['nisn' => '0012345678']))->toThrow(QueryException::class);

    $lama->delete();

    expect(siswaDi($this->smp, ['nisn' => '0012345678'])->exists)->toBeTrue();
});

it('mengizinkan banyak siswa tanpa NISN', function () {
    siswaDi($this->smp, ['nisn' => null]);
    siswaDi($this->smp, ['nisn' => null]);

    expect(Siswa::count())->toBe(2);
});

it('membatasi satu ayah, satu ibu, dan satu wali per siswa pada pivot', function () {
    $siswa = siswaDi($this->smp);
    $siswa->wali()->attach(WaliSiswa::factory()->create()->getKey(), ['hubungan' => 'ayah']);

    expect(fn () => $siswa->wali()->attach(WaliSiswa::factory()->create()->getKey(), ['hubungan' => 'ayah']))
        ->toThrow(QueryException::class);
});

it('membagi satu orang tua antara kakak dan adik', function () {
    $ayah = WaliSiswa::factory()->create();
    $kakak = siswaDi($this->smp);
    $adik = siswaDi($this->smp);
    $kakak->wali()->attach($ayah->getKey(), ['hubungan' => 'ayah']);
    $adik->wali()->attach($ayah->getKey(), ['hubungan' => 'ayah']);

    expect($ayah->siswa)->toHaveCount(2)
        ->and($kakak->load('wali')->waliDengan(HubunganWali::Ayah)->is($ayah))->toBeTrue();
});

// ---------------------------------------------------------------- daftar

it('menampilkan hanya siswa unit sendiri untuk petugas TU unit', function () {
    siswaDi($this->smp, ['nama_siswa' => 'Siswa SMP Satu']);
    siswaDi($this->smk, ['nama_siswa' => 'Siswa SMK Satu']);

    $this->actingAs($this->tuSmp)->get(route('admin.siswa.index'))
        ->assertOk()
        ->assertSee('Siswa SMP Satu')
        ->assertDontSee('Siswa SMK Satu');
});

it('menampilkan semua unit untuk super_admin dan pemilik yayasan', function (Role $role) {
    siswaDi($this->smp, ['nama_siswa' => 'Siswa SMP Satu']);
    siswaDi($this->smk, ['nama_siswa' => 'Siswa SMK Satu']);

    $this->actingAs(akun($role))->get(route('admin.siswa.index'))
        ->assertSee('Siswa SMP Satu')
        ->assertSee('Siswa SMK Satu');
})->with([Role::SuperAdmin, Role::PemilikYayasan]);

it('mencari berdasarkan nama atau NISN dan memfilter status serta kelas', function () {
    $a = siswaDi($this->smp, ['nama_siswa' => 'Andi Pratama', 'nisn' => '0011111111']);
    siswaDi($this->smp, ['nama_siswa' => 'Budi Hartono', 'nisn' => '0022222222', 'status_siswa' => 'calon']);
    $kelas = kelasDi($this->smp);
    AnggotaKelas::create(['id_siswa' => $a->id_siswa, 'id_kelas' => $kelas->id_kelas]);

    $this->actingAs($this->tuSmp);

    $this->get(route('admin.siswa.index', ['q' => 'andi']))->assertSee('Andi Pratama')->assertDontSee('Budi Hartono');
    $this->get(route('admin.siswa.index', ['q' => '0022']))->assertSee('Budi Hartono')->assertDontSee('Andi Pratama');
    $this->get(route('admin.siswa.index', ['status_siswa' => 'calon']))->assertSee('Budi Hartono')->assertDontSee('Andi Pratama');
    $this->get(route('admin.siswa.index', ['id_kelas' => $kelas->id_kelas]))->assertSee('Andi Pratama')->assertDontSee('Budi Hartono');
});

it('memaginasi 25 siswa per halaman tanpa N+1 yang berlebihan', function () {
    Siswa::factory()->count(30)->create(['id_unit_sekolah' => $this->smp->id_unit_sekolah]);

    DB::enableQueryLog();
    $this->actingAs($this->tuSmp)->get(route('admin.siswa.index'))->assertOk()->assertSee('dari 30 data');
    $jumlah = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($jumlah)->toBeLessThan(40);
});

it('menolak role tanpa izin lihat siswa dan tamu', function (Role $role) {
    $this->actingAs(akun($role, $this->smp))->get(route('admin.siswa.index'))->assertForbidden();
})->with([Role::GuruMapel, Role::GuruPiket, Role::Pendaftar]);

it('mengarahkan tamu ke login', function () {
    $this->get(route('admin.siswa.index'))->assertRedirect(route('admin.login'));
});

// ---------------------------------------------------------------- wali kelas

it('membatasi wali kelas pada siswa kelas yang ia ampu', function () {
    $wali = akun(Role::WaliKelas, $this->smp, JenisPegawai::Guru);
    $kelasSaya = kelasDi($this->smp, 'VII-A', null, $wali->id_pegawai);
    $kelasLain = kelasDi($this->smp, 'VII-B');

    $milikku = siswaDi($this->smp, ['nama_siswa' => 'Murid Saya']);
    $bukanMilikku = siswaDi($this->smp, ['nama_siswa' => 'Murid Kelas Lain']);
    $tanpaKelas = siswaDi($this->smp, ['nama_siswa' => 'Murid Tanpa Kelas']);
    AnggotaKelas::create(['id_siswa' => $milikku->id_siswa, 'id_kelas' => $kelasSaya->id_kelas]);
    AnggotaKelas::create(['id_siswa' => $bukanMilikku->id_siswa, 'id_kelas' => $kelasLain->id_kelas]);

    $this->actingAs($wali);

    $this->get(route('admin.siswa.index'))->assertSee('Murid Saya')->assertDontSee('Murid Kelas Lain')->assertDontSee('Murid Tanpa Kelas');
    $this->get(route('admin.siswa.show', $milikku))->assertOk();
    $this->get(route('admin.siswa.show', $bukanMilikku))->assertForbidden();
    $this->get(route('admin.siswa.show', $tanpaKelas))->assertForbidden();

    // hanya lihat: tidak bisa ubah atau menempatkan
    $this->get(route('admin.siswa.edit', $milikku))->assertForbidden();
    $this->put(route('admin.siswa.update', $milikku), payloadSiswa())->assertForbidden();
    $this->post(route('admin.siswa.kelas.store', $milikku), ['id_kelas' => $kelasLain->id_kelas])->assertForbidden();

    $this->get(route('admin.kelas.anggota', $kelasSaya))->assertOk()->assertSee('Murid Saya');
    $this->get(route('admin.kelas.anggota', $kelasLain))->assertForbidden();
});

it('hanya menghitung kelas tahun ajaran aktif bagi wali kelas', function () {
    $wali = akun(Role::WaliKelas, $this->smp, JenisPegawai::Guru);
    $lama = buatTahunAjaran('2025/2026', false);
    $kelasLama = kelasDi($this->smp, 'VII-LAMA', $lama->id_tahun_ajaran, $wali->id_pegawai);
    $murid = siswaDi($this->smp, ['nama_siswa' => 'Murid Tahun Lalu']);
    AnggotaKelas::create(['id_siswa' => $murid->id_siswa, 'id_kelas' => $kelasLama->id_kelas]);

    $this->actingAs($wali)->get(route('admin.siswa.index'))->assertDontSee('Murid Tahun Lalu');
    $this->get(route('admin.siswa.show', $murid))->assertForbidden();
});

it('menampilkan menu Siswa untuk wali kelas tetapi tidak untuk guru mapel', function () {
    $this->actingAs(akun(Role::WaliKelas, $this->smp, JenisPegawai::Guru))
        ->get(route('admin.dashboard'))
        ->assertSee(route('admin.siswa.index'), false);

    $this->actingAs(akun(Role::GuruMapel, $this->smp, JenisPegawai::Guru))
        ->get(route('admin.dashboard'))
        ->assertDontSee(route('admin.siswa.index'), false);
});

// ---------------------------------------------------------------- detail

it('menampilkan detail siswa lengkap dengan orang tua', function () {
    $siswa = siswaDi($this->smp, ['nama_siswa' => 'Citra Lestari', 'tanggal_lahir' => '2012-03-09']);
    $siswa->wali()->attach(WaliSiswa::factory()->create(['nama_wali_siswa' => 'Pak Ayah Citra'])->getKey(), ['hubungan' => 'ayah']);

    $this->actingAs($this->tuSmp)->get(route('admin.siswa.show', $siswa))
        ->assertOk()
        ->assertSee('Citra Lestari')
        ->assertSee('09/03/2012')
        ->assertSee('Pak Ayah Citra')
        ->assertSee('Belum ditempatkan di kelas');
});

it('menampilkan detail untuk kepala sekolah unit yang sama, dan 404 untuk unit lain', function () {
    $milik = siswaDi($this->smp);
    $lain = siswaDi($this->smk);

    $this->actingAs(akun(Role::KepalaSekolah, $this->smp, JenisPegawai::Guru));
    $this->get(route('admin.siswa.show', $milik))->assertOk()->assertDontSee('Ubah Data');
    $this->get(route('admin.siswa.show', $lain))->assertNotFound();
});

// ---------------------------------------------------------------- ubah

it('mengubah identitas siswa dan orang tua oleh petugas TU', function () {
    $siswa = siswaDi($this->smp);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.siswa.update', $siswa), payloadSiswa([
            'nama_siswa' => 'Nama Baru',
            'nisn' => '0099999999',
            'no_hp' => '+62 812-0000-1111',
            'wali' => ['ayah' => ['pekerjaan' => 'pns', 'no_hp' => '0813 2222 3333'], 'wali' => ['nama_wali_siswa' => 'Paman Uji', 'hubungan_keluarga' => 'Paman']],
        ]))
        ->assertRedirect(route('admin.siswa.show', $siswa))
        ->assertSessionHasNoErrors();

    $siswa = $siswa->fresh()->load('wali');
    expect($siswa->nama_siswa)->toBe('Nama Baru')
        ->and($siswa->nisn)->toBe('0099999999')
        ->and($siswa->no_hp)->toBe('081200001111')
        ->and($siswa->waliDengan(HubunganWali::Ayah)->nama_wali_siswa)->toBe('Ayah Uji')
        ->and($siswa->waliDengan(HubunganWali::Ayah)->pekerjaan->value)->toBe('pns')
        ->and($siswa->waliDengan(HubunganWali::Ayah)->no_hp)->toBe('081322223333')
        ->and($siswa->waliDengan(HubunganWali::Ibu)->nama_wali_siswa)->toBe('Ibu Uji')
        ->and($siswa->waliDengan(HubunganWali::Wali)->pivot->hubungan_keluarga)->toBe('Paman');
});

it('memperbarui data orang tua yang dipakai bersama saudara kandung', function () {
    $ayah = WaliSiswa::factory()->create(['nama_wali_siswa' => 'Ayah Lama']);
    $kakak = siswaDi($this->smp);
    $adik = siswaDi($this->smp);
    $kakak->wali()->attach($ayah->getKey(), ['hubungan' => 'ayah']);
    $adik->wali()->attach($ayah->getKey(), ['hubungan' => 'ayah']);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.siswa.update', $kakak), payloadSiswa(['wali' => ['ayah' => ['nama_wali_siswa' => 'Ayah Baru']]]))
        ->assertSessionHasNoErrors();

    expect($adik->fresh()->load('wali')->waliDengan(HubunganWali::Ayah)->nama_wali_siswa)->toBe('Ayah Baru')
        ->and(WaliSiswa::count())->toBe(2); // ayah tetap satu baris; ibu baru dibuat untuk kakak
});

it('menghapus wali opsional bila namanya dikosongkan', function () {
    $siswa = siswaDi($this->smp);
    $siswa->wali()->attach(WaliSiswa::factory()->create()->getKey(), ['hubungan' => 'wali', 'hubungan_keluarga' => 'Paman']);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.siswa.update', $siswa), payloadSiswa(['wali' => ['wali' => ['nama_wali_siswa' => '']]]))
        ->assertSessionHasNoErrors();

    expect($siswa->fresh()->load('wali')->waliDengan(HubunganWali::Wali))->toBeNull();
});

it('memvalidasi data siswa', function (array $timpa, string $kolom) {
    $siswa = siswaDi($this->smp);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.siswa.update', $siswa), payloadSiswa($timpa))
        ->assertSessionHasErrors($kolom);
})->with([
    'nama kosong' => [['nama_siswa' => ''], 'nama_siswa'],
    'jenis kelamin salah' => [['jenis_kelamin' => 'x'], 'jenis_kelamin'],
    'NISN bukan 10 digit' => [['nisn' => '12345'], 'nisn'],
    'tanggal lahir di masa depan' => [['tanggal_lahir' => '2999-01-01'], 'tanggal_lahir'],
    'agama tidak dikenal' => [['agama' => 'lain'], 'agama'],
    'kode pos salah' => [['kode_pos' => '12'], 'kode_pos'],
    'no hp salah' => [['no_hp' => '12345'], 'no_hp'],
    'email salah' => [['email' => 'bukan-email'], 'email'],
    'nama ayah kosong' => [['wali' => ['ayah' => ['nama_wali_siswa' => '']]], 'wali.ayah.nama_wali_siswa'],
    'nama ibu kosong' => [['wali' => ['ibu' => ['nama_wali_siswa' => '']]], 'wali.ibu.nama_wali_siswa'],
    'pekerjaan ayah salah' => [['wali' => ['ayah' => ['pekerjaan' => 'astronot']]], 'wali.ayah.pekerjaan'],
]);

it('menampilkan pesan validasi berbahasa Indonesia dengan nama bidang yang ramah', function () {
    $siswa = siswaDi($this->smp);

    $this->actingAs($this->tuSmp)
        ->put(route('admin.siswa.update', $siswa), payloadSiswa(['wali' => ['ayah' => ['nama_wali_siswa' => '']]]))
        ->assertSessionHasErrors(['wali.ayah.nama_wali_siswa' => 'Nama Ayah wajib diisi.']);
});

it('menolak NISN yang sudah dipakai siswa lain tanpa membocorkan datanya, tetapi mengizinkan NISN sendiri', function () {
    siswaDi($this->smp, ['nisn' => '0011111111', 'nama_siswa' => 'Pemilik Lain']);
    $siswa = siswaDi($this->smp, ['nisn' => '0022222222']);

    $this->actingAs($this->tuSmp);

    $respon = $this->put(route('admin.siswa.update', $siswa), payloadSiswa(['nisn' => '0011111111']));
    $respon->assertSessionHasErrors(['nisn' => 'NISN ini sudah terdaftar.']);
    expect(collect(session('errors')->all())->implode(' '))->not->toContain('Pemilik Lain');

    $this->put(route('admin.siswa.update', $siswa), payloadSiswa(['nisn' => '0022222222']))->assertSessionHasNoErrors();
});

it('tidak mengubah siswa unit lain dan menolak role tanpa siswa.update', function () {
    $lain = siswaDi($this->smk);
    $milik = siswaDi($this->smp);

    $this->actingAs($this->tuSmp)->put(route('admin.siswa.update', $lain), payloadSiswa())->assertNotFound();

    $this->actingAs(akun(Role::KepalaSekolah, $this->smp, JenisPegawai::Guru));
    $this->get(route('admin.siswa.edit', $milik))->assertForbidden();
    $this->put(route('admin.siswa.update', $milik), payloadSiswa())->assertForbidden();

    $this->actingAs(akun(Role::PemilikYayasan));
    $this->put(route('admin.siswa.update', $milik), payloadSiswa())->assertForbidden();
});

it('membatalkan seluruh perubahan bila penyimpanan orang tua gagal (transaksi)', function () {
    $siswa = siswaDi($this->smp, ['nama_siswa' => 'Nama Awal']);

    // Lewati validasi dengan memanggil Action langsung memakai data wali yang merusak: kolom tidak ada.
    expect(fn () => app(SimpanDataSiswa::class)($siswa, [
        'nama_siswa' => 'Nama Berubah',
        'wali' => ['ayah' => ['nama_wali_siswa' => str_repeat('x', 300)]], // melebihi panjang kolom
    ]))->toThrow(QueryException::class);

    expect($siswa->fresh()->nama_siswa)->toBe('Nama Awal');
});

// ---------------------------------------------------------------- penempatan kelas

it('menempatkan siswa ke kelas dan memindahkannya dalam tahun ajaran yang sama', function () {
    $siswa = siswaDi($this->smp);
    $a = kelasDi($this->smp, 'VII-A');
    $b = kelasDi($this->smp, 'VII-B');

    $this->actingAs($this->tuSmp);

    $this->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $a->id_kelas])->assertRedirect(route('admin.siswa.show', $siswa));
    $this->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $b->id_kelas])->assertSessionHasNoErrors();

    expect(AnggotaKelas::where('id_siswa', $siswa->id_siswa)->count())->toBe(1)
        ->and(AnggotaKelas::firstWhere('id_siswa', $siswa->id_siswa)->id_kelas)->toBe($b->id_kelas);
});

it('membuat penempatan baru pada tahun ajaran lain', function () {
    $siswa = siswaDi($this->smp);
    $tahunLain = buatTahunAjaran('2027/2028', false);
    $a = kelasDi($this->smp, 'VII-A');
    $b = kelasDi($this->smp, 'VIII-A', $tahunLain->id_tahun_ajaran);

    $this->actingAs($this->tuSmp);
    $this->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $a->id_kelas]);
    $this->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $b->id_kelas]);

    expect(AnggotaKelas::where('id_siswa', $siswa->id_siswa)->count())->toBe(2);
});

it('menolak kelas dari unit lain dan status siswa yang tidak dapat ditempatkan', function () {
    $siswa = siswaDi($this->smp);
    $kelasSmk = kelasDi($this->smk, 'X RPL 1');
    $lulus = siswaDi($this->smp, ['status_siswa' => 'lulus']);
    $kelasSmp = kelasDi($this->smp);

    $this->actingAs($this->tuSmp);

    $this->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $kelasSmk->id_kelas])->assertSessionHasErrors('id_kelas');
    $this->post(route('admin.siswa.kelas.store', $lulus), ['id_kelas' => $kelasSmp->id_kelas])->assertSessionHas('error');

    expect(AnggotaKelas::count())->toBe(0);
});

it('mengeluarkan siswa dari kelas dan menolak penghapusan anggota milik siswa lain', function () {
    $siswa = siswaDi($this->smp);
    $lain = siswaDi($this->smp);
    $kelas = kelasDi($this->smp);
    $anggota = AnggotaKelas::create(['id_siswa' => $siswa->id_siswa, 'id_kelas' => $kelas->id_kelas]);

    $this->actingAs($this->tuSmp);
    $this->delete(route('admin.siswa.kelas.destroy', [$lain, $anggota]))->assertNotFound();
    $this->delete(route('admin.siswa.kelas.destroy', [$siswa, $anggota]))->assertRedirect(route('admin.siswa.show', $siswa));

    expect(AnggotaKelas::count())->toBe(0);
});

it('menolak penempatan oleh role tanpa siswa.update', function () {
    $siswa = siswaDi($this->smp);
    $kelas = kelasDi($this->smp);

    $this->actingAs(akun(Role::KepalaSekolah, $this->smp, JenisPegawai::Guru))
        ->post(route('admin.siswa.kelas.store', $siswa), ['id_kelas' => $kelas->id_kelas])
        ->assertForbidden();
});

// ---------------------------------------------------------------- anggota kelas

it('menampilkan anggota kelas berurutan nama untuk petugas TU dan menghitungnya di daftar kelas', function () {
    $kelas = kelasDi($this->smp);
    foreach (['Zaki', 'Adi', 'Mira'] as $nama) {
        AnggotaKelas::create(['id_siswa' => siswaDi($this->smp, ['nama_siswa' => $nama])->id_siswa, 'id_kelas' => $kelas->id_kelas]);
    }

    $this->actingAs($this->tuSmp)
        ->get(route('admin.kelas.anggota', $kelas))
        ->assertOk()
        ->assertSeeInOrder(['Adi', 'Mira', 'Zaki']);

    $this->get(route('admin.kelas.index'))->assertSee(route('admin.kelas.anggota', $kelas), false);
});

it('menolak melihat anggota kelas unit lain dan role tanpa izin', function () {
    $kelasSmk = kelasDi($this->smk, 'X RPL 1');
    $kelasSmp = kelasDi($this->smp);

    $this->actingAs($this->tuSmp)->get(route('admin.kelas.anggota', $kelasSmk))->assertNotFound();

    $this->actingAs(akun(Role::GuruMapel, $this->smp, JenisPegawai::Guru))
        ->get(route('admin.kelas.anggota', $kelasSmp))
        ->assertForbidden();
});

it('menolak penghapusan kelas yang masih memiliki siswa', function () {
    $kelas = kelasDi($this->smp);
    AnggotaKelas::create(['id_siswa' => siswaDi($this->smp)->id_siswa, 'id_kelas' => $kelas->id_kelas]);

    $this->actingAs($this->tuSmp)->from(route('admin.kelas.index'))
        ->delete(route('admin.kelas.destroy', $kelas))
        ->assertSessionHas('error');

    expect(Kelas::find($kelas->id_kelas))->not->toBeNull();
});
