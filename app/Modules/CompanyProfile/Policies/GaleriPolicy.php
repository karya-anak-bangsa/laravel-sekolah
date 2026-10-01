<?php

namespace App\Modules\CompanyProfile\Policies;

use App\Support\PermissionPolicy;

class GaleriPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'galeri';
    }
}
