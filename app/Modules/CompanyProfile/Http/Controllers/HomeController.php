<?php

namespace App\Modules\CompanyProfile\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(): View
    {
        return view('company-profile::home');
    }
}
