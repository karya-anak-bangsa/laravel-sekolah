<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class AktifkanTahunAjaran
{
    /** Menjadikan satu tahun ajaran aktif dan menonaktifkan yang lain, secara atomik. */
    public function __invoke(TahunAjaran $tahunAjaran): void
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->where('is_aktif', true)->lockForUpdate()->update(['is_aktif' => false]);
            $tahunAjaran->update(['is_aktif' => true]);
        });
    }
}
