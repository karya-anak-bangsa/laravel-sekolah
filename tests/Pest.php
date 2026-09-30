<?php

use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Enums\Semester;
use App\Modules\Core\Models\TahunAjaran;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
 * Helper bersama untuk feature test master data.
 */

/** Akun pegawai dengan satu role; bila $unit diberikan, pegawainya ditugaskan ke unit itu (tingkat unit). */
function akun(Role $role, ?UnitSekolah $unit = null, JenisPegawai $jenis = JenisPegawai::Tu): User
{
    app(RolePermissionSeeder::class)->run();

    $pegawai = Pegawai::create([
        'id_unit_sekolah' => $unit?->id_unit_sekolah,
        'nama_pegawai' => 'Pegawai '.$role->label(),
        'jenis_pegawai' => $jenis,
    ]);

    $user = User::factory()->create(['id_pegawai' => $pegawai->id_pegawai, 'nama' => $pegawai->nama_pegawai]);
    $user->assignRole($role->value);

    return $user;
}

function buatUnit(Jenjang $jenjang): UnitSekolah
{
    return UnitSekolah::create(['nama_unit_sekolah' => $jenjang->label().' Uji', 'jenjang' => $jenjang]);
}

function buatTahunAjaran(string $nama = '2026/2027', bool $aktif = true): TahunAjaran
{
    return TahunAjaran::create(['nama_tahun_ajaran' => $nama, 'semester_aktif' => Semester::Ganjil, 'is_aktif' => $aktif]);
}
