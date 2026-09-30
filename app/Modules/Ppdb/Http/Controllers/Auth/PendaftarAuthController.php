<?php

namespace App\Modules\Ppdb\Http\Controllers\Auth;

use App\Modules\Core\Enums\LoginArea;
use App\Modules\Core\Services\LoginService;
use App\Modules\Ppdb\Http\Requests\PendaftarLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PendaftarAuthController
{
    public function create(): View
    {
        return view('ppdb::auth.login');
    }

    public function store(PendaftarLoginRequest $request, LoginService $login): RedirectResponse
    {
        $login->masuk(
            LoginArea::Ppdb,
            $request->validated('no_hp'),
            $request->validated('password'),
            $request->boolean('remember'),
            (string) $request->ip(),
        );

        $request->session()->regenerate();

        // Area pendaftar (formulir, status) dibuat di Fase 3; sementara kembali ke beranda.
        return redirect()->intended(Route::has('ppdb.index') ? route('ppdb.index') : route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppdb.login');
    }
}
