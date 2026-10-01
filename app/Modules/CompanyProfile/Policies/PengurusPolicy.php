<?php

namespace App\Modules\CompanyProfile\Policies;

use App\Support\PermissionPolicy;

class PengurusPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'pengurus';
    }
}
