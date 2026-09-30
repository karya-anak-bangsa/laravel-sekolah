<?php

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
    $this->smp = buatUnit(Jenjang::Smp);
    $this->smk = buatUnit(Jenjang::Smk);
    $this->tuSmp = akun(Role::PetugasTu, $this->smp);
    $this->admin = akun(Role::SuperAdmin);
    $this->guruSmp = Pegawai::create(['id_unit_sekolah' => $this->smp->id_unit_sekolah, 'nama_pegawai' => 'Budi Santoso, S.Pd', 'jenis_pegawai' => JenisPegawai::Guru]);
    $this->guruSmk = Pegawai::create(['id_unit_sekolah' => $this->smk->id_unit_sekolah, 'nama_pegawai' => 'Guru SMK', 'jenis_pegawai' => JenisPegawai::Guru]);
});

it('membuat akun pegawai dengan username otomatis, sandi sementara, dan tanpa role oleh petugas TU', function () {
    $response = $this->actingAs($this->tuSmp)
        ->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai, 'roles' => ['kepala_sekolah']])
        ->assertRedirect(route('admin.pengguna.index'));

    $akun = User::where('id_pegawai', $this->guruSmp->id_pegawai)->firstOrFail();
    $flash = session('akun_baru');

    expect($akun->username)->toBe('budi.santoso.s.pd')
        ->and($akun->wajib_ganti_password)->toBeTrue()
        ->and($akun->roles)->toHaveCount(0) // TU tidak berwenang memberi role
        ->and(Hash::check($flash['password'], $akun->password))->toBeTrue()
        ->and(strlen($flash['password']))->toBe(10);

    $this->get(route('admin.pengguna.index'))->assertSee($flash['password'])->assertSee('hanya tampil sekali', false);
    $this->get(route('admin.pengguna.index'))->assertDontSee($flash['password']);
});

it('memberi role saat super_admin membuat akun', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai, 'username' => 'Budi.S', 'roles' => ['guru_mapel', 'wali_kelas']])
        ->assertSessionHasNoErrors();

    $akun = User::firstWhere('username', 'budi.s');
    expect($akun->getRoleNames()->sort()->values()->all())->toBe(['guru_mapel', 'wali_kelas']);
});

it('menambah angka pada username otomatis bila sudah dipakai, termasuk akun nonaktif', function () {
    $lain = Pegawai::create(['id_unit_sekolah' => $this->smp->id_unit_sekolah, 'nama_pegawai' => 'Budi Santoso, S.Pd', 'jenis_pegawai' => JenisPegawai::Tu]);
    $lama = User::factory()->create(['username' => 'budi.santoso.s.pd']);
    $lama->delete();

    $this->actingAs($this->tuSmp)->post(route('admin.pengguna.store'), ['id_pegawai' => $lain->id_pegawai]);

    expect(User::where('id_pegawai', $lain->id_pegawai)->value('username'))->toBe('budi.santoso.s.pd2');
});

it('menolak username ganda atau berformat salah dan pegawai yang sudah punya akun', function () {
    User::factory()->create(['username' => 'dipakai']);
    $this->actingAs($this->tuSmp);

    $this->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai, 'username' => 'dipakai'])->assertSessionHasErrors('username');
    $this->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai, 'username' => 'a b!'])->assertSessionHasErrors('username');

    $this->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai])->assertSessionHasNoErrors();
    $this->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmp->id_pegawai])->assertSessionHasErrors('id_pegawai');
});

it('menolak petugas TU unit SMP membuat akun untuk pegawai unit SMK', function () {
    $this->actingAs($this->tuSmp)
        ->post(route('admin.pengguna.store'), ['id_pegawai' => $this->guruSmk->id_pegawai])
        ->assertSessionHasErrors('id_pegawai');

    expect(User::where('id_pegawai', $this->guruSmk->id_pegawai)->exists())->toBeFalse();
});

it('membatasi daftar akun petugas TU ke unitnya dan menyembunyikan akun pendaftar', function () {
    $akunSmp = User::factory()->create(['id_pegawai' => $this->guruSmp->id_pegawai, 'nama' => 'Akun Guru SMP']);
    User::factory()->create(['id_pegawai' => $this->guruSmk->id_pegawai, 'nama' => 'Akun Guru SMK']);
    $pendaftar = User::factory()->pendaftar()->create(['nama' => 'Orang Tua Pendaftar']);
    $pendaftar->assignRole(Role::Pendaftar->value);

    $this->actingAs($this->tuSmp)->get(route('admin.pengguna.index'))
        ->assertSee('Akun Guru SMP')
        ->assertDontSee('Akun Guru SMK')
        ->assertDontSee('Orang Tua Pendaftar')
        ->assertDontSee($this->admin->nama);

    $this->actingAs($this->admin)->get(route('admin.pengguna.index'))
        ->assertSee('Akun Guru SMP')
        ->assertSee('Akun Guru SMK')
        ->assertDontSee('Orang Tua Pendaftar');
});

it('menolak petugas TU mengelola akun unit lain dan akun super_admin', function () {
    $akunSmk = User::factory()->create(['id_pegawai' => $this->guruSmk->id_pegawai]);

    $this->actingAs($this->tuSmp);
    $this->get(route('admin.pengguna.edit', $akunSmk))->assertForbidden();
    $this->post(route('admin.pengguna.reset-password', $akunSmk))->assertForbidden();
    $this->get(route('admin.pengguna.edit', $this->admin))->assertForbidden();
    $this->post(route('admin.pengguna.reset-password', $this->admin))->assertForbidden();
});

it('mengubah username dan role: hanya super_admin yang dapat mengubah role', function () {
    $akun = User::factory()->create(['id_pegawai' => $this->guruSmp->id_pegawai, 'username' => 'lama']);
    $akun->assignRole('guru_mapel');

    $this->actingAs($this->tuSmp)
        ->put(route('admin.pengguna.update', $akun), ['username' => 'baru', 'roles' => ['kepala_sekolah']])
        ->assertSessionHasNoErrors();
    expect($akun->fresh()->username)->toBe('baru')->and($akun->fresh()->getRoleNames()->all())->toBe(['guru_mapel']);

    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $akun), ['username' => 'baru', 'roles' => ['kepala_sekolah', 'guru_piket']])
        ->assertSessionHasNoErrors();
    expect($akun->fresh()->getRoleNames()->sort()->values()->all())->toBe(['guru_piket', 'kepala_sekolah']);
});

it('menolak role yang tidak dikenal atau pendaftar pada akun pegawai', function () {
    $akun = User::factory()->create(['id_pegawai' => $this->guruSmp->id_pegawai]);

    $this->actingAs($this->admin)
        ->put(route('admin.pengguna.update', $akun), ['username' => 'x1y', 'roles' => ['pendaftar']])
        ->assertSessionHasErrors('roles.0');
});

it('tidak mengizinkan super_admin mencabut role super_admin dari akunnya sendiri', function () {
    $this->actingAs($this->admin)->from(route('admin.pengguna.edit', $this->admin))
        ->put(route('admin.pengguna.update', $this->admin), ['username' => 'root1', 'roles' => ['guru_mapel']])
        ->assertSessionHas('error');

    expect($this->admin->fresh()->hasRole('super_admin'))->toBeTrue();
});

it('mereset kata sandi dan mewajibkan penggantian', function () {
    $akun = User::factory()->create(['id_pegawai' => $this->guruSmp->id_pegawai, 'password' => 'lama-lama-1']);

    $this->actingAs($this->tuSmp)
        ->post(route('admin.pengguna.reset-password', $akun))
        ->assertRedirect(route('admin.pengguna.index'));

    $baru = session('akun_baru')['password'];
    expect(Hash::check($baru, $akun->fresh()->password))->toBeTrue()
        ->and(Hash::check('lama-lama-1', $akun->fresh()->password))->toBeFalse()
        ->and($akun->fresh()->wajib_ganti_password)->toBeTrue();
});

it('menonaktifkan dan mengaktifkan kembali akun hanya oleh yang berwenang', function () {
    $akun = User::factory()->create(['id_pegawai' => $this->guruSmp->id_pegawai, 'username' => 'guru1']);

    // TU tidak punya pengguna.delete
    $this->actingAs($this->tuSmp)->delete(route('admin.pengguna.destroy', $akun))->assertForbidden();

    $this->actingAs($this->admin)->delete(route('admin.pengguna.destroy', $akun))->assertSessionHas('status');
    expect(User::find($akun->id_user))->toBeNull();

    $this->actingAs($this->tuSmp)->post(route('admin.pengguna.pulihkan', $akun->id_user))->assertForbidden();
    $this->actingAs($this->admin)->post(route('admin.pengguna.pulihkan', $akun->id_user))->assertSessionHas('status');
    expect(User::find($akun->id_user))->not->toBeNull();
});

it('menolak akun nonaktif untuk login', function () {
    $akun = User::factory()->create(['username' => 'nonaktif1', 'password' => 'rahasia123']);
    $akun->delete();

    $this->post(route('admin.login.store'), ['username' => 'nonaktif1', 'password' => 'rahasia123'])->assertSessionHasErrors('username');
    $this->assertGuest();
});

it('tidak mengizinkan menonaktifkan akun sendiri', function () {
    $this->actingAs($this->admin)->from(route('admin.pengguna.index'))
        ->delete(route('admin.pengguna.destroy', $this->admin))
        ->assertSessionHas('error');

    expect(User::find($this->admin->id_user))->not->toBeNull();
});

it('menolak role tanpa pengguna.view', function (Role $role) {
    $this->actingAs(akun($role, $this->smp))->get(route('admin.pengguna.index'))->assertForbidden();
})->with([Role::KepalaSekolah, Role::PemilikYayasan, Role::WaliKelas, Role::GuruMapel, Role::GuruPiket, Role::Pendaftar]);
