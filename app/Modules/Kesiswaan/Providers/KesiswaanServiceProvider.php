<?php

namespace App\Modules\Kesiswaan\Providers;

use App\Support\ModuleServiceProvider;

class KesiswaanServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'kesiswaan';
    }
}
