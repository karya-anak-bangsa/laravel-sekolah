<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Models\User;
use App\Support\PermissionPolicy;

/** Jurusan dikelola bersama unit sekolah: memakai permission unit-sekolah.*. */
class JurusanPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'unit-sekolah';
    }

    public function update(User $user, mixed $jurusan = null): bool
    {
        return $this->izin($user, 'update') && $this->unitDiizinkan($user, $jurusan);
    }

    public function delete(User $user, mixed $jurusan = null): bool
    {
        return $this->izin($user, 'delete') && $this->unitDiizinkan($user, $jurusan);
    }

    private function unitDiizinkan(User $user, mixed $jurusan): bool
    {
        return $jurusan === null || $user->dapatMengaksesUnit($jurusan->id_unit_sekolah);
    }
}
