<?php

use App\Modules\Core\Models\User;
use App\Support\Menu;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->withoutVite();
    // Di aplikasi, middleware web membagikan $errors ke semua view; tiru di sini.
    View::share('errors', new ViewErrorBag);
});

/** Daftarkan route bernama saat test, lalu segarkan name lookup agar Route::has()/route() mengenalnya. */
function rute(string $method, string $uri, string $nama): void
{
    Route::{$method}($uri, fn () => 'ok')->name($nama);
    app('router')->getRoutes()->refreshNameLookups();
}

function halamanAdmin(string $isi = '<p>isi halaman</p>'): string
{
    return Blade::render(<<<BLADE
        @extends('layouts.admin')
        @section('title', 'Uji Halaman')
        @section('content')
        {$isi}
        @endsection
    BLADE);
}

it('menampilkan beranda publik dengan layout UniPulse', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(config('sekolah.nama'))
        ->assertSee('id="navmenu"', escape: false)
        ->assertSee('lang="id"', escape: false);
});

it('merender layout admin dengan judul, konten, dan tanpa Bootstrap', function () {
    $html = halamanAdmin();

    expect($html)
        ->toContain('<title>Uji Halaman | '.config('app.name').'</title>')
        ->toContain('<h1 class="page-title">Uji Halaman</h1>')
        ->toContain('isi halaman')
        ->toContain('data-shell="admin"')
        ->not->toContain('bootstrap');
});

it('menampilkan menu admin hanya untuk route yang ada dan permission yang dimiliki', function () {
    rute('get', '/uji/pegawai', 'admin.pegawai.index');
    Permission::create(['name' => 'dashboard.view', 'guard_name' => 'web']);
    Permission::create(['name' => 'pegawai.view', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user);
    $labels = collect(Menu::admin($user))->pluck('items')->flatten(1)->pluck('text');

    // Dashboard: route ada + permission ada. Pegawai: route ada tapi tanpa permission.
    // Siswa dkk: permission tidak ada dan route belum terdaftar -> tidak tampil.
    expect($labels->all())->toBe(['Dashboard']);

    $user->givePermissionTo('pegawai.view');
    $labels = collect(Menu::admin($user->fresh()))->pluck('items')->flatten(1)->pluck('text');

    expect($labels->all())->toBe(['Dashboard', 'Pegawai']);
});

it('tidak menampilkan menu admin untuk tamu', function () {
    expect(Menu::admin(null))->toBe([]);
});

it('menandai item menu aktif sesuai route yang sedang dibuka', function () {
    rute('get', '/uji/pegawai', 'admin.pegawai.index');
    rute('get', '/uji/pegawai/{id}', 'admin.pegawai.show');
    Permission::create(['name' => 'pegawai.view', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->givePermissionTo('pegawai.view');

    $this->actingAs($user)->get('/uji/pegawai/1');
    $item = collect(Menu::admin($user))->pluck('items')->flatten(1)->firstWhere('text', 'Pegawai');

    expect($item['active'])->toBeTrue();
});

it('merender nama pengguna di sidebar dan tombol keluar memakai POST dengan CSRF', function () {
    $this->actingAs(User::factory()->create(['nama' => 'Budi Santoso']));

    $rendered = halamanAdmin();

    expect($rendered)
        ->toContain('Budi Santoso')
        ->toContain('action="'.route('admin.logout').'"')
        ->toContain('name="_token"');
});

it('komponen x-form.input menampilkan error dan nilai lama dengan kelas Gentelella', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['nama' => ['Nama wajib diisi.']])));

    $html = Blade::render('<x-form.input name="nama" label="Nama" required />');

    expect($html)
        ->toContain('class="form-control is-invalid"')
        ->toContain('<div class="form-error">Nama wajib diisi.</div>')
        ->toContain('class="required"');
});

it('komponen x-form.input memakai kelas Bootstrap pada theme public', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['no_hp' => ['No HP tidak valid.']])));

    $html = Blade::render('<x-form.input name="no_hp" label="No HP" theme="public" />');

    expect($html)
        ->toContain('class="form-control is-invalid"')
        ->toContain('invalid-feedback d-block')
        ->not->toContain('form-error');
});

it('komponen x-form.input tidak mengisi ulang password', function () {
    $html = Blade::render('<x-form.input name="password" type="password" :value="\'rahasia\'" />');

    expect($html)->not->toContain('rahasia');
});

it('komponen x-form.select menandai opsi terpilih', function () {
    $html = Blade::render(
        '<x-form.select name="jenjang" :options="[\'smp\' => \'SMP\', \'smk\' => \'SMK\']" value="smk" />',
    );

    expect($html)->toContain('<option value="smk" selected>SMK</option>');
});
