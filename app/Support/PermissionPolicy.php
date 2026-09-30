<?php

namespace App\Support;

use App\Modules\Core\Models\User;

/**
 * Dasar Policy berbasis permission: aksi "<sumber-daya>.<aksi>" (lihat App\Modules\Core\Support\Izin).
 * super_admin sudah lolos lebih dulu lewat Gate::before. Policy turunan menambah aturan data
 * (mis. unit/kelas sendiri) di atas pengecekan permission ini.
 */
abstract class PermissionPolicy
{
    /** Awalan permission, mis. "kelas" untuk kelas.view, kelas.create, dst. */
    abstract protected function sumberDaya(): string;

    protected function izin(User $user, string $aksi): bool
    {
        return $user->checkPermissionTo($this->sumberDaya().'.'.$aksi);
    }

    public function viewAny(User $user): bool
    {
        return $this->izin($user, 'view');
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->izin($user, 'create');
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'update');
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'delete');
    }
}
