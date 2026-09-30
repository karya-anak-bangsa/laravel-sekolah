<?php

use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use App\Modules\Kesiswaan\Models\Siswa;
use App\Modules\Kesiswaan\Models\WaliSiswa;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DataDummySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    app(DataDummySeeder::class)->dengan(2, 3)->run();
});

it('membuat 84 pegawai dengan komposisi yayasan, guru, dan TU', function () {
    $semua = Pegawai::withoutGlobalScopes();

    expect($semua->count())->toBe(84)
        ->and(Pegawai::withoutGlobalScopes()->where('jenis_pegawai', JenisPegawai::PimpinanYayasan)->whereNull('id_unit_sekolah')->count())->toBe(1)
        ->and(Pegawai::withoutGlobalScopes()->where('jenis_pegawai', JenisPegawai::Tu)->count())->toBe(6)
        ->and(Pegawai::withoutGlobalScopes()->where('jenis_pegawai', JenisPegawai::Guru)->count())->toBe(77);
});

it('membuat 12 rombel SMP dan 24 rombel SMK sesuai skala klien', function () {
    $kelas = Kelas::withoutGlobalScopes()->with('unitSekolah')->get();

    expect($kelas)->toHaveCount(36)
        ->and($kelas->where('unitSekolah.jenjang.value', 'smp'))->toHaveCount(12)
        ->and($kelas->where('unitSekolah.jenjang.value', 'smk'))->toHaveCount(24)
        ->and($kelas->where('unitSekolah.jenjang.value', 'smp')->whereNotNull('id_jurusan'))->toHaveCount(0)
        ->and($kelas->where('unitSekolah.jenjang.value', 'smk')->whereNull('id_jurusan'))->toHaveCount(0)
        ->and($kelas->where('unitSekolah.jenjang.value', 'smp')->groupBy('tingkat')->map->count()->all())->toBe([7 => 4, 8 => 4, 9 => 4])
        ->and($kelas->where('unitSekolah.jenjang.value', 'smk')->groupBy('id_jurusan')->map->count()->unique()->values()->all())->toBe([6]);
});

it('menunjuk wali kelas guru unit yang sama dan berbeda untuk tiap kelas', function () {
    $kelas = Kelas::withoutGlobalScopes()->with('waliKelas')->get();

    expect($kelas->pluck('id_pegawai_wali_kelas')->unique())->toHaveCount(36)
        ->and($kelas->every(fn ($k) => $k->waliKelas->jenis_pegawai === JenisPegawai::Guru && $k->waliKelas->id_unit_sekolah === $k->id_unit_sekolah))->toBeTrue();
});

it('memberi akun dan role pada setiap pegawai sesuai perannya', function () {
    expect(User::whereNotNull('id_pegawai')->count())->toBe(84)
        ->and(User::role(Role::PemilikYayasan->value)->count())->toBe(1)
        ->and(User::role(Role::KepalaSekolah->value)->count())->toBe(2)
        ->and(User::role(Role::WakilKepalaSekolah->value)->count())->toBe(4)
        ->and(User::role(Role::WaliKelas->value)->count())->toBe(36)
        ->and(User::role(Role::PetugasTu->value)->count())->toBe(6)
        ->and(User::role(Role::GuruPiket->value)->count())->toBe(6)
        ->and(User::role(Role::GuruMapel->value)->count())->toBe(77)
        ->and(User::whereNotNull('id_pegawai')->distinct('username')->count('username'))->toBe(84);
});

it('membuat siswa aktif dengan ayah, ibu, dan satu kelas masing-masing', function () {
    $siswa = Siswa::withoutGlobalScopes()->with('wali')->get();

    expect($siswa->count())->toBeBetween(72, 108)
        ->and($siswa->every(fn ($s) => $s->wali->count() === 2 && $s->waliDengan(HubunganWali::Ayah) && $s->waliDengan(HubunganWali::Ibu)))->toBeTrue()
        ->and(AnggotaKelas::count())->toBe($siswa->count())
        ->and($siswa->pluck('nisn')->unique()->count())->toBe($siswa->count())
        ->and($siswa->every(fn ($s) => $s->status_siswa->value === 'aktif'))->toBeTrue();
});

it('menyimpan data ber-format Indonesia yang valid untuk aturan validasi aplikasi', function () {
    $siswa = Siswa::withoutGlobalScopes()->first();

    expect($siswa->nisn)->toMatch('/^\d{10}$/')
        ->and($siswa->no_hp)->toMatch('/^08\d{8,12}$/')
        ->and($siswa->kode_pos)->toMatch('/^\d{5}$/')
        ->and($siswa->tanggal_lahir->lt(now()))->toBeTrue();
});

it('menjalankan ulang tidak menggandakan data', function () {
    app(DataDummySeeder::class)->dengan(2, 3)->run();

    expect(Pegawai::withoutGlobalScopes()->count())->toBe(84)
        ->and(Kelas::withoutGlobalScopes()->count())->toBe(36);
});

it('membuat sebagian siswa berbagi orang tua dengan saudara kandungnya', function () {
    // Dengan ±6% peluang, seed besar hampir pasti punya saudara; di sini cukup pastikan struktur pivot mengizinkannya.
    $jumlahWali = WaliSiswa::count();
    $jumlahSiswa = Siswa::withoutGlobalScopes()->count();

    expect($jumlahWali)->toBeLessThanOrEqual($jumlahSiswa * 2);
});

it('tidak membuat data dummy di produksi', function () {
    DB::table('tb_anggota_kelas')->delete();
    DB::table('tb_siswa_wali')->delete();
    DB::table('tb_wali_siswa')->delete();
    DB::table('tb_siswa')->delete();
    DB::table('tb_kelas')->delete();
    DB::table('tb_user')->delete();
    DB::table('tb_pegawai')->delete();

    app()->detectEnvironment(fn () => 'production');
    app(DataDummySeeder::class)->run();

    expect(Pegawai::withoutGlobalScopes()->count())->toBe(0);
});

it('hanya dipanggil oleh DatabaseSeeder di environment lokal', function () {
    DB::table('tb_anggota_kelas')->delete();
    DB::table('tb_siswa_wali')->delete();
    DB::table('tb_wali_siswa')->delete();
    DB::table('tb_siswa')->delete();
    DB::table('tb_kelas')->delete();
    DB::table('tb_user')->delete();
    DB::table('tb_pegawai')->delete();

    // environment testing: data dasar dibuat, data dummy tidak.
    app(DatabaseSeeder::class)->run();

    expect(Pegawai::withoutGlobalScopes()->count())->toBe(0);
});
