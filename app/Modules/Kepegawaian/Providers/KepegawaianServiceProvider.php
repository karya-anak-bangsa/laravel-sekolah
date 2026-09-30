<?php

namespace App\Modules\Kepegawaian\Providers;

use App\Support\ModuleServiceProvider;

class KepegawaianServiceProvider extends ModuleServiceProvider
{
    protected function viewNamespace(): string
    {
        return 'kepegawaian';
    }
}
