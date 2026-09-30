<?php

namespace App\Modules\Core\Models\Scopes;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Pengguna tingkat unit (pegawai dengan id_unit_sekolah terisi) hanya melihat data unitnya.
 * Tanpa pengguna login (console/seeder), super_admin, dan pegawai tingkat yayasan
 * (id_unit_sekolah null, mis. pemilik yayasan) melihat semua unit.
 */
class UnitSekolahScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $idUnit = $user->idUnitSekolah();

        if ($idUnit !== null) {
            $builder->where($model->qualifyColumn('id_unit_sekolah'), $idUnit);
        }
    }
}
