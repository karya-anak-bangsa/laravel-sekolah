<?php

namespace App\Modules\Core\Providers;

use App\Support\ModuleServiceProvider;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'core';
    }
}
