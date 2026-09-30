<?php

use App\Modules\CompanyProfile\Providers\CompanyProfileServiceProvider;
use App\Modules\Core\Providers\CoreServiceProvider;
use App\Modules\Kepegawaian\Providers\KepegawaianServiceProvider;
use App\Modules\Kesiswaan\Providers\KesiswaanServiceProvider;
use App\Modules\Ppdb\Providers\PpdbServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    KepegawaianServiceProvider::class,
    KesiswaanServiceProvider::class,
    CompanyProfileServiceProvider::class,
    PpdbServiceProvider::class,
];
