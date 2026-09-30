<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\User;
use App\Support\PermissionPolicy;

/**
 * Kelola akun pegawai. Aturan tambahan di atas permission:
 *  - pengguna tingkat unit hanya mengelola akun pegawai di unitnya;
 *  - akun super_admin hanya boleh dikelola super_admin.
 * Catatan: super_admin lolos Policy lewat Gate::before, jadi larangan menonaktifkan akun sendiri
 * ditegakkan di PenggunaController, bukan di sini.
 */
class PenggunaPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'pengguna';
    }

    public function view(User $user, mixed $pengguna = null): bool
    {
        return $this->izin($user, 'view') && $this->dapatMengelola($user, $pengguna);
    }

    public function update(User $user, mixed $pengguna = null): bool
    {
        return $this->izin($user, 'update') && $this->dapatMengelola($user, $pengguna);
    }

    public function resetPassword(User $user, User $pengguna): bool
    {
        return $this->update($user, $pengguna);
    }

    public function delete(User $user, mixed $pengguna = null): bool
    {
        return $this->izin($user, 'delete') && $this->dapatMengelola($user, $pengguna);
    }

    public function pulihkan(User $user, User $pengguna): bool
    {
        return $this->izin($user, 'delete') && $this->dapatMengelola($user, $pengguna);
    }

    public function assignRole(User $user): bool
    {
        return $user->checkPermissionTo('pengguna.assign-role');
    }

    private function dapatMengelola(User $user, mixed $pengguna): bool
    {
        if ($pengguna === null) {
            return true;
        }

        if ($pengguna->hasRole(Role::SuperAdmin->value) && ! $user->hasRole(Role::SuperAdmin->value)) {
            return false;
        }

        return $user->dapatMengaksesUnit($pengguna->idUnitSekolah());
    }
}
