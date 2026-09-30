<?php

use App\Modules\Kepegawaian\Http\Controllers\PegawaiController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.area', 'password.changed'])->group(function () {
    Route::resource('pegawai', PegawaiController::class)->except('show')->parameters(['pegawai' => 'pegawai']);
});
