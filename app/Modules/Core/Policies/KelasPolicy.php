<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Models\User;
use App\Support\UnitPermissionPolicy;

// Kelas di unit lain sudah tersaring UnitSekolahScope (404); pengecekan unit di sini sebagai lapisan kedua.
class KelasPolicy extends UnitPermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'kelas';
    }

    /**
     * Melihat daftar siswa dalam kelas: kelas.view + siswa.view di unitnya, atau wali kelas
     * (siswa.view-kelas) untuk kelas yang ia ampu pada tahun ajaran aktif.
     */
    public function viewAnggota(User $user, mixed $kelas): bool
    {
        if ($this->izin($user, 'view') && $user->checkPermissionTo('siswa.view')) {
            return $user->dapatMengaksesUnit($kelas->id_unit_sekolah);
        }

        return $user->checkPermissionTo('siswa.view-kelas')
            && $kelas->newQuery()->whereKey($kelas->getKey())->diampuOleh($user)->exists();
    }
}
