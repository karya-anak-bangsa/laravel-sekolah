<?php

namespace App\Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        return view('core::dashboard');
    }
}
