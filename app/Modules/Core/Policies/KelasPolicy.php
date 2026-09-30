<?php

namespace App\Modules\Core\Policies;

use App\Support\UnitPermissionPolicy;

// Kelas di unit lain sudah tersaring UnitSekolahScope (404); pengecekan unit di sini sebagai lapisan kedua.
class KelasPolicy extends UnitPermissionPolicy
{
    protected function sumberDaya(): string
    {
        return 'kelas';
    }
}
