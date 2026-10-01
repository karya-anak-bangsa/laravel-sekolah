<?php

use App\Modules\CompanyProfile\Http\Controllers\HomeController;
use App\Modules\CompanyProfile\Http\Controllers\KontakController;
use App\Modules\CompanyProfile\Http\Controllers\PengaturanController;
use App\Modules\CompanyProfile\Http\Controllers\PengurusController;
use App\Modules\CompanyProfile\Http\Controllers\TentangController;
use Illuminate\Support\Facades\Route;

// Situs publik
Route::get('/', HomeController::class)->name('home');
Route::get('tentang', TentangController::class)->name('tentang');
Route::get('kontak', KontakController::class)->name('kontak');

// Panel admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.area', 'password.changed'])->group(function () {
    Route::get('pengaturan-situs', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::put('pengaturan-situs', [PengaturanController::class, 'update'])->name('pengaturan.update');

    Route::resource('pengurus', PengurusController::class)->except('show')->parameters(['pengurus' => 'pengurus']);
});
