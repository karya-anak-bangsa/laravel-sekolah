<?php

use App\Modules\Core\Database\Seeders\CoreSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

function akunSementara(): User
{
    $user = akun(Role::GuruMapel, null, JenisPegawai::Guru);
    $user->update(['password' => 'Sementara1', 'wajib_ganti_password' => true]);

    return $user;
}

it('mengarahkan pengguna dengan sandi sementara ke halaman ganti kata sandi dari halaman admin mana pun', function () {
    $this->actingAs(akunSementara());

    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.password.edit'));
    $this->get(route('admin.pegawai.index'))->assertRedirect(route('admin.password.edit'));
    $this->get(route('admin.password.edit'))->assertOk()->assertSee('Ganti kata sandi');
});

it('tetap mengizinkan keluar saat sandi masih sementara', function () {
    $this->actingAs(akunSementara())
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));
});

it('mengganti kata sandi dan menghapus kewajiban ganti', function () {
    $user = akunSementara();

    $this->actingAs($user)
        ->put(route('admin.password.update'), ['password_lama' => 'Sementara1', 'password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123'])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status');

    expect(Hash::check('SandiBaru123', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->wajib_ganti_password)->toBeFalse();

    $this->get(route('admin.dashboard'))->assertOk();
});

it('menerima password sederhana asal minimal 8 karakter, tanpa syarat kombinasi', function (string $baru) {
    $user = akunSementara();

    $this->actingAs($user)
        ->put(route('admin.password.update'), ['password_lama' => 'Sementara1', 'password' => $baru, 'password_confirmation' => $baru])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    expect(Hash::check($baru, $user->fresh()->password))->toBeTrue();
})->with(['huruf kecil saja' => 'hurufsaja', 'angka saja' => '12345678', 'tepat 8 karakter' => 'abcdefgh']);

it('menolak penggantian dengan sandi lama salah, konfirmasi beda, terlalu pendek, atau sama dengan yang lama', function (array $input, string $kolom) {
    $user = akunSementara();

    $this->actingAs($user)
        ->put(route('admin.password.update'), $input)
        ->assertSessionHasErrors($kolom);

    expect(Hash::check('Sementara1', $user->fresh()->password))->toBeTrue();
})->with([
    'sandi lama salah' => [['password_lama' => 'salah', 'password' => 'SandiBaru123', 'password_confirmation' => 'SandiBaru123'], 'password_lama'],
    'konfirmasi beda' => [['password_lama' => 'Sementara1', 'password' => 'SandiBaru123', 'password_confirmation' => 'Lain12345'], 'password'],
    'terlalu pendek' => [['password_lama' => 'Sementara1', 'password' => 'Ab1', 'password_confirmation' => 'Ab1'], 'password'],
    'tujuh karakter' => [['password_lama' => 'Sementara1', 'password' => 'abcdefg', 'password_confirmation' => 'abcdefg'], 'password'],
    'sama dengan lama' => [['password_lama' => 'Sementara1', 'password' => 'Sementara1', 'password_confirmation' => 'Sementara1'], 'password'],
]);

it('dapat diakses pengguna biasa tanpa sandi sementara dan tidak untuk tamu atau pendaftar', function () {
    $this->get(route('admin.password.edit'))->assertRedirect(route('admin.login'));

    $this->actingAs(akun(Role::Pendaftar))->get(route('admin.password.edit'))->assertForbidden();

    $this->actingAs(akun(Role::GuruMapel))->get(route('admin.password.edit'))->assertOk();
});

it('menampilkan tautan ganti kata sandi di menu akun', function () {
    $this->actingAs(akun(Role::GuruMapel))
        ->get(route('admin.dashboard'))
        ->assertSee(route('admin.password.edit'), false);
});

it('mewajibkan akun Super Administrator awal dari seeder mengganti sandi', function () {
    config(['sekolah.admin_awal' => ['nama' => 'Super Administrator', 'username' => 'admin.uji', 'password' => 'Kata-Sandi-Uji-1']]);
    $this->seed(CoreSeeder::class);

    $this->post(route('admin.login.store'), ['username' => 'admin.uji', 'password' => 'Kata-Sandi-Uji-1'])
        ->assertRedirect(route('admin.dashboard'));

    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.password.edit'));
});
