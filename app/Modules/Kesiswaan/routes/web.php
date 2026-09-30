<?php

use App\Modules\Kesiswaan\Http\Controllers\AnggotaKelasController;
use App\Modules\Kesiswaan\Http\Controllers\PenempatanKelasController;
use App\Modules\Kesiswaan\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.area', 'password.changed'])->group(function () {
    // Siswa baru masuk lewat PPDB; di sini hanya daftar, detail, dan perubahan data.
    Route::resource('siswa', SiswaController::class)->only(['index', 'show', 'edit', 'update'])->parameters(['siswa' => 'siswa']);

    Route::post('siswa/{siswa}/kelas', [PenempatanKelasController::class, 'store'])->name('siswa.kelas.store');
    Route::delete('siswa/{siswa}/kelas/{anggota_kelas}', [PenempatanKelasController::class, 'destroy'])->name('siswa.kelas.destroy');

    Route::get('kelas/{kelas}/anggota', [AnggotaKelasController::class, 'index'])->name('kelas.anggota');
});
