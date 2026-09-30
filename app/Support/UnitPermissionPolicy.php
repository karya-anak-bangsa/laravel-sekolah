<?php

namespace App\Support;

use App\Modules\Core\Models\User;

/**
 * PermissionPolicy untuk data yang terikat unit sekolah: selain permission, pengguna tingkat unit
 * hanya boleh menyentuh data unitnya (lapisan kedua setelah UnitSekolahScope).
 */
abstract class UnitPermissionPolicy extends PermissionPolicy
{
    /** Unit pemilik data; override bila kolom unitnya bukan id_unit_sekolah. */
    protected function idUnit(mixed $model): ?int
    {
        return $model->id_unit_sekolah;
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'view') && $this->unitDiizinkan($user, $model);
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'update') && $this->unitDiizinkan($user, $model);
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $this->izin($user, 'delete') && $this->unitDiizinkan($user, $model);
    }

    private function unitDiizinkan(User $user, mixed $model): bool
    {
        return $model === null || $user->dapatMengaksesUnit($this->idUnit($model));
    }
}
