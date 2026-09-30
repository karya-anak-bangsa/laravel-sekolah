<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Models\User;
use App\Support\PermissionPolicy;

class KelasPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'kelas';
    }

    // Kelas di unit lain sudah tersaring UnitSekolahScope (404); pengecekan eksplisit sebagai lapisan kedua.
    public function view(User $user, mixed $kelas = null): bool
    {
        return $this->izin($user, 'view') && $this->unitDiizinkan($user, $kelas);
    }

    public function update(User $user, mixed $kelas = null): bool
    {
        return $this->izin($user, 'update') && $this->unitDiizinkan($user, $kelas);
    }

    public function delete(User $user, mixed $kelas = null): bool
    {
        return $this->izin($user, 'delete') && $this->unitDiizinkan($user, $kelas);
    }

    private function unitDiizinkan(User $user, mixed $kelas): bool
    {
        return $kelas === null || $user->dapatMengaksesUnit($kelas->id_unit_sekolah);
    }
}
