<?php

namespace App\Modules\Core\Database\Seeders;

use App\Modules\Core\Enums\Role;
use App\Modules\Core\Support\Izin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

/** Idempotent: aman dijalankan ulang (termasuk di produksi) untuk menyelaraskan role dan permission dengan kode. */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Izin::semua() as $nama) {
            Permission::findOrCreate($nama, 'web');
        }

        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value, 'web')->syncPermissions($role->permissions());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
