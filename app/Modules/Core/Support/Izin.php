<?php

namespace App\Modules\Core\Support;

/**
 * Katalog permission (format "<sumber-daya>.<aksi>"). Satu-satunya tempat nama permission didefinisikan;
 * kode lain memakai method di sini, dan otorisasi selalu lewat Gate/Policy berbasis permission ini.
 */
final class Izin
{
    public const DASHBOARD_VIEW = 'dashboard.view';

    /** Sumber daya yang memiliki aksi view/create/update/delete. */
    public const SUMBER_DAYA = [
        'pegawai',
        'siswa',
        'kelas',
        'tahun-ajaran',
        'unit-sekolah',
        'ppdb',
        'berita',
        'pengguna',
    ];

    public const AKSI_CRUD = ['view', 'create', 'update', 'delete'];

    /** Permission di luar pola CRUD. */
    public const KHUSUS = [
        'pengguna.assign-role', // mengubah role pengguna, hanya super_admin
    ];

    /** Seluruh permission sistem (dipakai seeder dan untuk super_admin). */
    public static function semua(): array
    {
        $crud = [];

        foreach (self::SUMBER_DAYA as $sumberDaya) {
            array_push($crud, ...self::crud($sumberDaya));
        }

        return [self::DASHBOARD_VIEW, ...$crud, ...self::KHUSUS];
    }

    /** @return list<string> view, create, update, delete untuk satu sumber daya. */
    public static function crud(string $sumberDaya): array
    {
        return array_map(fn (string $aksi) => "{$sumberDaya}.{$aksi}", self::AKSI_CRUD);
    }

    /** @return list<string> hanya aksi view untuk beberapa sumber daya. */
    public static function lihat(string ...$sumberDaya): array
    {
        return array_map(fn (string $item) => "{$item}.view", $sumberDaya);
    }
}
