<?php

namespace App\Modules\Core\Http\Controllers\Auth;

use App\Modules\Core\Enums\LoginArea;
use App\Modules\Core\Http\Requests\AdminLoginRequest;
use App\Modules\Core\Services\LoginService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController
{
    public function create(): View
    {
        return view('core::auth.login');
    }

    public function store(AdminLoginRequest $request, LoginService $login): RedirectResponse
    {
        $login->masuk(
            LoginArea::Admin,
            $request->validated('username'),
            $request->validated('password'),
            $request->boolean('remember'),
            (string) $request->ip(),
        );

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
