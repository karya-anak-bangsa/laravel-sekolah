<?php

use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->withoutVite();
    $this->seed(RolePermissionSeeder::class);
});

function penggunaDenganRole(Role ...$roles): User
{
    $user = User::factory()->create();
    $user->assignRole(array_map(fn (Role $role) => $role->value, $roles));

    return $user;
}

it('menampilkan dashboard untuk role yang punya dashboard.view', function (Role $role) {
    $this->actingAs(penggunaDenganRole($role))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang');
})->with([
    Role::SuperAdmin, Role::PemilikYayasan, Role::KepalaSekolah, Role::WakilKepalaSekolah,
    Role::PetugasTu, Role::WaliKelas, Role::GuruMapel, Role::GuruPiket,
]);

it('menolak akun pendaftar dari /admin dengan 403', function () {
    $this->actingAs(penggunaDenganRole(Role::Pendaftar))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('menolak pendaftar yang juga diberi permission dashboard.view', function () {
    $user = penggunaDenganRole(Role::Pendaftar);
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

it('menolak pegawai tanpa role/permission dengan 403', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('menampilkan menu sesuai permission, bukan nama role', function () {
    // Belum ada route untuk modul lain, jadi hanya Dashboard yang boleh muncul pada semua role.
    $this->actingAs(penggunaDenganRole(Role::PetugasTu))
        ->get(route('admin.dashboard'))
        ->assertSee('Dashboard')
        ->assertDontSee('Pegawai');
});

it('membuat super_admin lolos semua permission tanpa diberi satu per satu', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::SuperAdmin->value);

    expect(Gate::forUser($super)->allows('company-profile.apa-saja'))->toBeTrue()
        ->and(Gate::forUser($super)->allows('ppdb.delete'))->toBeTrue();

    $tu = penggunaDenganRole(Role::PetugasTu);
    expect(Gate::forUser($tu)->allows('ppdb.delete'))->toBeTrue()
        ->and(Gate::forUser($tu)->allows('pengguna.assign-role'))->toBeFalse();
});
