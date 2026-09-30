<?php

use App\Modules\Core\Database\Seeders\CoreSeeder;
use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Database\Seeders\SuperAdminSeeder;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\Izin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleModel;

it('membuat 9 role dan seluruh permission, dan aman dijalankan ulang', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(RoleModel::count())->toBe(9)
        ->and(Permission::count())->toBe(count(Izin::semua()))
        ->and(RoleModel::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->map->value->sort()->values()->all());
});

it('memberi super_admin semua permission', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(RoleModel::findByName('super_admin')->permissions)->toHaveCount(count(Izin::semua()));
});

it('menerapkan matriks permission yang disepakati', function () {
    $this->seed(RolePermissionSeeder::class);
    $punya = fn (string $role, string $izin) => RoleModel::findByName($role)->hasPermissionTo($izin);

    // Lihat-saja untuk pimpinan
    foreach (['pemilik_yayasan', 'kepala_sekolah', 'wakil_kepala_sekolah'] as $role) {
        expect($punya($role, 'dashboard.view'))->toBeTrue()
            ->and($punya($role, 'pegawai.view'))->toBeTrue()
            ->and($punya($role, 'ppdb.view'))->toBeTrue()
            ->and($punya($role, 'ppdb.create'))->toBeFalse()
            ->and($punya($role, 'pegawai.update'))->toBeFalse();
    }

    // Petugas TU: kelola PPDB, siswa, pegawai, berita; bukan kelas/tahun ajaran/ubah role
    expect($punya('petugas_tu', 'ppdb.delete'))->toBeTrue()
        ->and($punya('petugas_tu', 'siswa.update'))->toBeTrue()
        ->and($punya('petugas_tu', 'pegawai.create'))->toBeTrue()
        ->and($punya('petugas_tu', 'berita.create'))->toBeTrue()
        ->and($punya('petugas_tu', 'kelas.update'))->toBeFalse()
        ->and($punya('petugas_tu', 'pengguna.assign-role'))->toBeFalse();

    // Wali kelas, guru mapel, guru piket
    expect($punya('wali_kelas', 'siswa.view'))->toBeTrue()
        ->and($punya('wali_kelas', 'ppdb.view'))->toBeFalse()
        ->and($punya('guru_mapel', 'dashboard.view'))->toBeTrue()
        ->and($punya('guru_mapel', 'siswa.view'))->toBeFalse()
        ->and($punya('guru_piket', 'siswa.view'))->toBeFalse();

    // Pendaftar: tanpa permission panel admin
    expect(RoleModel::findByName('pendaftar')->permissions)->toHaveCount(0);
});

it('membuat akun super_admin awal dari konfigurasi dan tidak menimpa password yang sudah ada', function () {
    config(['sekolah.admin_awal' => ['nama' => 'Super Administrator', 'username' => 'admin.uji', 'password' => 'Kata-Sandi-Uji-1']]);

    $this->seed(CoreSeeder::class);

    $user = User::where('username', 'admin.uji')->firstOrFail();
    expect($user->hasRole('super_admin'))->toBeTrue()
        ->and(Hash::check('Kata-Sandi-Uji-1', $user->password))->toBeTrue()
        ->and($user->id_pegawai)->toBeNull();

    $user->update(['password' => 'sudah-diganti']);
    config(['sekolah.admin_awal.password' => 'Kata-Sandi-Uji-1']);
    $this->seed(SuperAdminSeeder::class);

    expect(User::where('username', 'admin.uji')->count())->toBe(1)
        ->and(Hash::check('sudah-diganti', $user->fresh()->password))->toBeTrue();
});

it('tidak membuat akun super_admin bila kredensial awal belum diisi', function () {
    config(['sekolah.admin_awal' => ['nama' => 'Super Administrator', 'username' => null, 'password' => null]]);

    $this->seed(CoreSeeder::class);

    expect(User::count())->toBe(0);
});
