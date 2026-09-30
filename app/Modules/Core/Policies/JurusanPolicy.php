<?php

namespace App\Modules\Core\Policies;

use App\Support\UnitPermissionPolicy;

/** Jurusan dikelola bersama unit sekolah: memakai permission unit-sekolah.*. */
class JurusanPolicy extends UnitPermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'unit-sekolah';
    }
}
