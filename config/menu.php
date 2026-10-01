<?php

/*
 * Definisi menu. Item hanya tampil bila:
 *   - route-nya terdaftar (modul yang belum dikerjakan otomatis tidak muncul), dan
 *   - (khusus admin) pengguna punya permission-nya. Otorisasi selalu berbasis permission, bukan nama role.
 *
 * Item admin : text, icon, route, permission (string atau daftar = salah satu), [active: pola routeIs() bila beda dari route]
 * Item publik: text, route, [children]
 */
return [
    'admin' => [
        [
            'label' => 'Umum',
            'items' => [
                ['text' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard', 'permission' => 'dashboard.view'],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['text' => 'Pegawai', 'icon' => 'users', 'route' => 'admin.pegawai.index', 'permission' => 'pegawai.view', 'active' => 'admin.pegawai.*'],
                ['text' => 'Siswa', 'icon' => 'profile', 'route' => 'admin.siswa.index', 'permission' => ['siswa.view', 'siswa.view-kelas'], 'active' => 'admin.siswa.*'],
                ['text' => 'Kelas', 'icon' => 'tables', 'route' => 'admin.kelas.index', 'permission' => 'kelas.view', 'active' => 'admin.kelas.*'],
                ['text' => 'Tahun Ajaran', 'icon' => 'calendar', 'route' => 'admin.tahun-ajaran.index', 'permission' => 'tahun-ajaran.view', 'active' => 'admin.tahun-ajaran.*'],
                ['text' => 'Unit & Jurusan', 'icon' => 'layout', 'route' => 'admin.unit-sekolah.index', 'permission' => 'unit-sekolah.view', 'active' => 'admin.unit-sekolah.*'],
            ],
        ],
        [
            'label' => 'PPDB',
            'items' => [
                ['text' => 'Pendaftaran', 'icon' => 'forms', 'route' => 'admin.ppdb.index', 'permission' => 'ppdb.view', 'active' => 'admin.ppdb.*'],
            ],
        ],
        [
            'label' => 'Situs',
            'items' => [
                ['text' => 'Pengaturan Situs', 'icon' => 'settings', 'route' => 'admin.pengaturan.edit', 'permission' => 'pengaturan.view', 'active' => 'admin.pengaturan.*'],
                ['text' => 'Galeri Foto', 'icon' => 'pages', 'route' => 'admin.galeri.index', 'permission' => 'galeri.view', 'active' => 'admin.galeri.*'],
                ['text' => 'Prestasi', 'icon' => 'forms', 'route' => 'admin.prestasi.index', 'permission' => 'prestasi.view', 'active' => 'admin.prestasi.*'],
                ['text' => 'Struktur Organisasi', 'icon' => 'users', 'route' => 'admin.pengurus.index', 'permission' => 'pengurus.view', 'active' => 'admin.pengurus.*'],
                ['text' => 'Berita & Pengumuman', 'icon' => 'pages', 'route' => 'admin.berita.index', 'permission' => 'berita.view', 'active' => 'admin.berita.*'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['text' => 'Pengguna & Role', 'icon' => 'settings', 'route' => 'admin.pengguna.index', 'permission' => 'pengguna.view', 'active' => 'admin.pengguna.*'],
            ],
        ],
    ],

    'public' => [
        ['text' => 'Beranda', 'route' => 'home'],
        ['text' => 'Tentang', 'route' => 'tentang'],
        ['text' => 'Berita', 'route' => 'berita.index'],
        ['text' => 'Galeri', 'route' => 'galeri'],
        ['text' => 'Prestasi', 'route' => 'prestasi'],
        ['text' => 'PPDB', 'route' => 'ppdb.index'],
        ['text' => 'Kontak', 'route' => 'kontak'],
    ],
];
