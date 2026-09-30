<?php

namespace App\View\Components\Admin;

use App\Support\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        return view('components.admin.sidebar', [
            'groups' => Menu::admin($user),
            'user' => $user,
        ]);
    }
}
