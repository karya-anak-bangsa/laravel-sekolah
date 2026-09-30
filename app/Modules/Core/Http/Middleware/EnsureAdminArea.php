<?php

namespace App\Modules\Core\Http\Middleware;

use App\Modules\Core\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Akun pendaftar PPDB hanya boleh mengakses area publik PPDB; ditolak dari /admin. */
class EnsureAdminArea
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole(Role::Pendaftar->value)) {
            abort(403);
        }

        return $next($request);
    }
}
