<?php

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pengguna dengan kata sandi sementara hanya boleh membuka halaman ganti kata sandi dan keluar. */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->wajib_ganti_password && ! $request->routeIs('admin.password.*', 'admin.logout')) {
            return redirect()->route('admin.password.edit')
                ->with('error', 'Anda harus mengganti kata sandi sementara sebelum melanjutkan.');
        }

        return $next($request);
    }
}
