<?php

use App\Modules\Core\Http\Controllers\Auth\AdminAuthController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\JurusanController;
use App\Modules\Core\Http\Controllers\KelasController;
use App\Modules\Core\Http\Controllers\TahunAjaranController;
use App\Modules\Core\Http\Controllers\UnitSekolahController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin.area'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)
            ->middleware('can:dashboard.view')
            ->name('dashboard');

        // Master data. Otorisasi per aksi lewat Policy di controller.
        Route::resource('unit-sekolah', UnitSekolahController::class)->except('show');

        Route::get('unit-sekolah/{unit_sekolah}/jurusan/create', [JurusanController::class, 'create'])->name('jurusan.create');
        Route::post('unit-sekolah/{unit_sekolah}/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
        Route::resource('jurusan', JurusanController::class)->only(['edit', 'update', 'destroy']);

        Route::resource('tahun-ajaran', TahunAjaranController::class)->except('show');
        Route::post('tahun-ajaran/{tahun_ajaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');

        Route::resource('kelas', KelasController::class)->except('show')->parameters(['kelas' => 'kelas']);
    });
});
