<?php

use App\Modules\CompanyProfile\Http\Controllers\BeritaController;
use App\Modules\CompanyProfile\Http\Controllers\BeritaPublikController;
use App\Modules\CompanyProfile\Http\Controllers\GaleriController;
use App\Modules\CompanyProfile\Http\Controllers\GaleriPublikController;
use App\Modules\CompanyProfile\Http\Controllers\HomeController;
use App\Modules\CompanyProfile\Http\Controllers\KontakController;
use App\Modules\CompanyProfile\Http\Controllers\PengaturanController;
use App\Modules\CompanyProfile\Http\Controllers\PengurusController;
use App\Modules\CompanyProfile\Http\Controllers\PrestasiController;
use App\Modules\CompanyProfile\Http\Controllers\PrestasiPublikController;
use App\Modules\CompanyProfile\Http\Controllers\TentangController;
use Illuminate\Support\Facades\Route;

// Situs publik
Route::get('/', HomeController::class)->name('home');
Route::get('tentang', TentangController::class)->name('tentang');
Route::get('kontak', KontakController::class)->name('kontak');
Route::get('galeri', GaleriPublikController::class)->name('galeri');
Route::get('prestasi', PrestasiPublikController::class)->name('prestasi');
Route::get('berita', [BeritaPublikController::class, 'index'])->name('berita.index');
Route::get('berita/{slug}', [BeritaPublikController::class, 'show'])->name('berita.show');

// Panel admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.area', 'password.changed'])->group(function () {
    Route::get('pengaturan-situs', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::put('pengaturan-situs', [PengaturanController::class, 'update'])->name('pengaturan.update');

    Route::resource('berita', BeritaController::class)->except('show')->parameters(['berita' => 'berita']);
    Route::resource('galeri', GaleriController::class)->except('show')->parameters(['galeri' => 'galeri']);
    Route::resource('prestasi', PrestasiController::class)->except('show')->parameters(['prestasi' => 'prestasi']);
    Route::resource('pengurus', PengurusController::class)->except('show')->parameters(['pengurus' => 'pengurus']);
});
