<?php

namespace App\Modules\CompanyProfile\Providers;

use App\Modules\CompanyProfile\Services\PengaturanSitus;
use App\Support\ModuleServiceProvider;

class CompanyProfileServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PengaturanSitus::class);
    }

    protected function viewNamespace(): string
    {
        return 'company-profile';
    }
}
