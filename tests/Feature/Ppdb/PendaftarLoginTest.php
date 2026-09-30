<?php

use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Modules\Core\Services\LoginService;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
    RateLimiter::clear('ppdb|081234567890|127.0.0.1');
});

function akunPendaftar(array $atribut = []): User
{
    $user = User::factory()->pendaftar()->create(['no_hp' => '081234567890', 'password' => 'rahasia123', ...$atribut]);
    $user->assignRole(Role::Pendaftar->value);

    return $user;
}

it('menampilkan halaman login pendaftar dengan layout publik', function () {
    $this->get(route('ppdb.login'))
        ->assertOk()
        ->assertSee('Masuk Pendaftar PPDB')
        ->assertSee('id="navmenu"', escape: false)
        ->assertDontSee('sidebar');
});

it('mengizinkan pendaftar masuk dengan nomor HP dan password', function () {
    $user = akunPendaftar();

    $this->post(route('ppdb.login.store'), ['no_hp' => '081234567890', 'password' => 'rahasia123'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('menerima nomor HP dalam format +62 atau dengan spasi dan strip', function (string $input) {
    $user = akunPendaftar();

    $this->post(route('ppdb.login.store'), ['no_hp' => $input, 'password' => 'rahasia123'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
})->with(['+62 812-3456-7890', '6281234567890', '0812 3456 7890']);

it('menolak password salah dan nomor tak dikenal dengan pesan yang sama', function () {
    akunPendaftar();

    $this->post(route('ppdb.login.store'), ['no_hp' => '081234567890', 'password' => 'salah'])
        ->assertSessionHasErrors(['no_hp' => 'Nomor HP atau kata sandi salah.']);
    $this->post(route('ppdb.login.store'), ['no_hp' => '089999999999', 'password' => 'rahasia123'])
        ->assertSessionHasErrors(['no_hp' => 'Nomor HP atau kata sandi salah.']);

    $this->assertGuest();
});

it('menolak akun pegawai di login pendaftar', function () {
    $pegawai = User::factory()->create(['no_hp' => '081234567890', 'password' => 'rahasia123']);
    $pegawai->assignRole(Role::PetugasTu->value);

    $this->post(route('ppdb.login.store'), ['no_hp' => '081234567890', 'password' => 'rahasia123'])
        ->assertSessionHasErrors(['no_hp' => 'Nomor HP atau kata sandi salah.']);

    $this->assertGuest();
});

it('membatasi percobaan login setelah 5 kali gagal', function () {
    akunPendaftar();

    foreach (range(1, LoginService::MAKS_PERCOBAAN) as $i) {
        $this->post(route('ppdb.login.store'), ['no_hp' => '081234567890', 'password' => 'salah']);
    }

    $this->post(route('ppdb.login.store'), ['no_hp' => '081234567890', 'password' => 'rahasia123'])
        ->assertSessionHasErrors('no_hp');

    expect(session('errors')->first('no_hp'))->toContain('Terlalu banyak percobaan masuk');
    $this->assertGuest();
});

it('mengeluarkan pendaftar lewat POST logout', function () {
    $this->actingAs(akunPendaftar())
        ->post(route('ppdb.logout'))
        ->assertRedirect(route('ppdb.login'));

    $this->assertGuest();
});

it('mengarahkan pendaftar yang sudah login dari halaman login ke beranda', function () {
    $this->actingAs(akunPendaftar())
        ->get(route('ppdb.login'))
        ->assertRedirect(route('home'));
});
