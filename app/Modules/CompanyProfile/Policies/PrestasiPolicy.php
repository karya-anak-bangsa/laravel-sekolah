<?php

namespace App\Modules\CompanyProfile\Policies;

use App\Support\PermissionPolicy;

class PrestasiPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'prestasi';
    }
}
