<?php

namespace App\Modules\Kepegawaian\Policies;

use App\Support\UnitPermissionPolicy;

class PegawaiPolicy extends UnitPermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'pegawai';
    }
}
