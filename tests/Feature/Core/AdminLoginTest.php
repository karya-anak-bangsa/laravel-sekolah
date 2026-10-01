<?php

use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\LoginService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
    RateLimiter::clear('admin|budi|127.0.0.1');
});

function pegawaiDenganRole(Role $role, array $atribut = []): User
{
    $user = User::factory()->create(['username' => 'budi', 'password' => 'rahasia123', ...$atribut]);
    $user->assignRole($role->value);

    return $user;
}

it('menampilkan halaman login admin', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('Masuk ke Panel Admin')
        ->assertSee('Yayasan Puspita Bangsa')
        ->assertSee('Gunakan username dan password kepegawaian Anda.')
        ->assertSee('name="username"', escape: false)
        ->assertSeeInOrder(['*</span>', 'Username'], escape: false)
        ->assertSeeInOrder(['*</span>', 'Password'], escape: false)
        ->assertDontSee('Kata sandi')
        ->assertSee('data-toggle-password="#password"', escape: false);
});

it('mengizinkan pegawai masuk dengan username dan password lalu menuju dashboard', function () {
    $user = pegawaiDenganRole(Role::PetugasTu);

    $this->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'rahasia123'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('tidak membedakan huruf besar/kecil pada username dan mengabaikan spasi di ujung', function () {
    $user = pegawaiDenganRole(Role::GuruMapel);

    $this->post(route('admin.login.store'), ['username' => '  BUDI ', 'password' => 'rahasia123'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('menolak password salah dan username tak dikenal dengan pesan yang sama', function () {
    pegawaiDenganRole(Role::PetugasTu);

    $salah = $this->from(route('admin.login'))
        ->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'salah']);
    $tak_ada = $this->from(route('admin.login'))
        ->post(route('admin.login.store'), ['username' => 'tidak.ada', 'password' => 'rahasia123']);

    $salah->assertRedirect(route('admin.login'))->assertSessionHasErrors(['username' => 'Username atau password salah.']);
    $tak_ada->assertRedirect(route('admin.login'))->assertSessionHasErrors(['username' => 'Username atau password salah.']);
    $this->assertGuest();
});

it('menolak akun pendaftar di login admin dengan pesan yang sama', function () {
    $pendaftar = User::factory()->pendaftar()->create(['username' => 'budi', 'password' => 'rahasia123']);
    $pendaftar->assignRole(Role::Pendaftar->value);

    $this->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'rahasia123'])
        ->assertSessionHasErrors(['username' => 'Username atau password salah.']);

    $this->assertGuest();
});

it('menolak akun yang sudah dihapus (soft delete)', function () {
    pegawaiDenganRole(Role::PetugasTu)->delete();

    $this->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'rahasia123'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('mewajibkan username dan password', function () {
    $this->post(route('admin.login.store'), [])
        ->assertSessionHasErrors(['username' => 'Username wajib diisi.', 'password' => 'Password wajib diisi.']);
});

it('membatasi percobaan login setelah 5 kali gagal, bahkan dengan password yang benar', function () {
    pegawaiDenganRole(Role::PetugasTu);

    foreach (range(1, LoginService::MAKS_PERCOBAAN) as $i) {
        $this->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'salah'])
            ->assertSessionHasErrors('username');
    }

    $this->post(route('admin.login.store'), ['username' => 'budi', 'password' => 'rahasia123'])
        ->assertStatus(302)
        ->assertSessionHasErrors('username');

    expect(session('errors')->first('username'))->toContain('Terlalu banyak percobaan masuk');
    $this->assertGuest();
});

it('mengeluarkan pengguna lewat POST logout dan menghapus sesi', function () {
    $user = pegawaiDenganRole(Role::PetugasTu);

    $this->actingAs($user)
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

it('tidak mengizinkan logout lewat GET', function () {
    $this->actingAs(pegawaiDenganRole(Role::PetugasTu))
        ->get('/admin/logout')
        ->assertStatus(405);
});

it('mengarahkan tamu yang membuka /admin ke halaman login admin', function () {
    $this->get('/admin')->assertRedirect(route('admin.login'));
});

it('mengarahkan pengguna yang sudah login dari halaman login ke dashboard', function () {
    $this->actingAs(pegawaiDenganRole(Role::PetugasTu))
        ->get(route('admin.login'))
        ->assertRedirect(route('admin.dashboard'));
});

it('menyimpan password ter-hash', function () {
    $user = pegawaiDenganRole(Role::PetugasTu);

    expect($user->password)->not->toBe('rahasia123')
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
});
