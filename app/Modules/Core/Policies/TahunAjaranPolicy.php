<?php

namespace App\Modules\Core\Policies;

use App\Support\PermissionPolicy;

class TahunAjaranPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'tahun-ajaran';
    }
}
