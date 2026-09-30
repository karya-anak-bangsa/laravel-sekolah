<?php

namespace App\Modules\Core\Policies;

use App\Modules\Core\Models\User;
use App\Support\PermissionPolicy;

class UnitSekolahPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'unit-sekolah';
    }

    public function view(User $user, mixed $unit = null): bool
    {
        return $this->izin($user, 'view') && $this->unitDiizinkan($user, $unit);
    }

    public function update(User $user, mixed $unit = null): bool
    {
        return $this->izin($user, 'update') && $this->unitDiizinkan($user, $unit);
    }

    public function delete(User $user, mixed $unit = null): bool
    {
        return $this->izin($user, 'delete') && $this->unitDiizinkan($user, $unit);
    }

    private function unitDiizinkan(User $user, mixed $unit): bool
    {
        return $unit === null || $user->dapatMengaksesUnit($unit->getKey());
    }
}
