<?php

namespace App\Modules\CompanyProfile\Providers;

use App\Support\ModuleServiceProvider;

class CompanyProfileServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'company-profile';
    }
}
