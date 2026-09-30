<?php

use App\Modules\Core\Database\Seeders\CoreSeeder;
use App\Modules\Core\Models\Jurusan;
use App\Modules\Core\Models\TahunAjaran;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Validator;

it('memakai locale id, fallback id, dan timezone Asia/Jakarta', function () {
    expect(app()->getLocale())->toBe('id')
        ->and(config('app.fallback_locale'))->toBe('id')
        ->and(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(config('app.faker_locale'))->toBe('id_ID');
});

it('menampilkan pesan validasi berbahasa Indonesia dengan nama atribut yang ramah', function () {
    $validator = Validator::make(
        ['no_hp' => '', 'nama_kelas' => str_repeat('a', 300)],
        ['no_hp' => 'required', 'nama_kelas' => 'max:255', 'username' => 'required'],
    );

    expect($validator->errors()->first('no_hp'))->toBe('Nomor HP wajib diisi.')
        ->and($validator->errors()->first('nama_kelas'))->toBe('Nama kelas tidak boleh lebih dari 255 karakter.')
        ->and($validator->errors()->first('username'))->toBe('Username wajib diisi.');
});

it('menerjemahkan seluruh kunci validasi bawaan framework', function () {
    $bawaan = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');
    $id = require base_path('lang/id/validation.php');

    $hilang = array_diff(array_keys($bawaan), array_keys($id));

    expect($hilang)->toBe([]);
});

it('menampilkan halaman error berbahasa Indonesia', function (int $kode, string $judul) {
    $this->withoutVite();

    // Menguji view error langsung; render lewat exception handler sama dengan view ini.
    $html = view('errors.'.$kode)->render();

    expect($html)->toContain((string) $kode)->toContain($judul)->toContain('Kembali ke beranda');
})->with([
    [403, 'Akses ditolak'],
    [404, 'Halaman tidak ditemukan'],
    [419, 'Sesi berakhir'],
    [429, 'Terlalu banyak permintaan'],
    [500, 'Terjadi kesalahan pada server'],
    [503, 'Sedang dalam pemeliharaan'],
]);

it('merender halaman 404 untuk URL yang tidak ada', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan');
});

it('merender halaman 403 saat akses admin ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden()
        ->assertSee('Akses ditolak');
});

it('menyemai unit SMP dan SMK, 4 jurusan SMK, dan tahun ajaran aktif tanpa duplikasi saat dijalankan ulang', function () {
    $this->seed(CoreSeeder::class);
    $this->seed(CoreSeeder::class);

    $smk = UnitSekolah::where('jenjang', 'smk')->firstOrFail();

    expect(UnitSekolah::count())->toBe(2)
        ->and(Jurusan::count())->toBe(4)
        ->and($smk->jurusan()->pluck('kode_jurusan')->sort()->values()->all())->toBe(['BM', 'PAR', 'RPL', 'TKJ'])
        ->and(UnitSekolah::where('jenjang', 'smp')->firstOrFail()->jurusan)->toHaveCount(0)
        ->and(TahunAjaran::count())->toBe(1)
        ->and(TahunAjaran::aktif()->count())->toBe(1);
});
