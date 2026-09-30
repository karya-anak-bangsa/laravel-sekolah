<?php

namespace App\Modules\Kesiswaan\Policies;

use App\Modules\Core\Models\User;
use App\Support\UnitPermissionPolicy;

class SiswaPolicy extends UnitPermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'siswa';
    }

    public function viewAny(User $user): bool
    {
        return $this->izin($user, 'view') || $user->checkPermissionTo('siswa.view-kelas');
    }

    /**
     * siswa.view: siswa di unitnya; siswa.view-kelas (wali kelas): hanya siswa di kelas yang ia ampu
     * pada tahun ajaran aktif.
     */
    public function view(User $user, mixed $siswa = null): bool
    {
        if (parent::view($user, $siswa)) {
            return true;
        }

        return $siswa !== null
            && $user->checkPermissionTo('siswa.view-kelas')
            && $siswa->anggotaKelas()->whereHas('kelas', fn ($k) => $k->diampuOleh($user))->exists();
    }
}
