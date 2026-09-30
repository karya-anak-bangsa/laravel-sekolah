<?php

namespace App\Modules\Ppdb\Providers;

use App\Support\ModuleServiceProvider;

class PpdbServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'ppdb';
    }
}
