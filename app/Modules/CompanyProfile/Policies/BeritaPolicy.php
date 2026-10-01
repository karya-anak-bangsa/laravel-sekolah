<?php

namespace App\Modules\CompanyProfile\Policies;

use App\Support\PermissionPolicy;

class BeritaPolicy extends PermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'berita';
    }
}
