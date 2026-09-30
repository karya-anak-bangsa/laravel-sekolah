<?php

use App\Support\AksiDitolak;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu diarahkan ke pintu masuk sesuai area; pengguna yang sudah login dikembalikan ke areanya.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('ppdb', 'ppdb/*')
            ? route('ppdb.login')
            : route('admin.login'));

        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->hasRole('pendaftar')
            ? (Route::has('ppdb.index') ? route('ppdb.index') : route('home'))
            : route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (AksiDitolak $e) => back()->with('error', $e->getMessage()));

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
