<?php

use App\Modules\Ppdb\Http\Controllers\Auth\PendaftarAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('ppdb')->name('ppdb.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [PendaftarAuthController::class, 'create'])->name('login');
        Route::post('login', [PendaftarAuthController::class, 'store'])->name('login.store');
    });

    Route::post('logout', [PendaftarAuthController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});
