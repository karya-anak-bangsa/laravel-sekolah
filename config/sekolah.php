<?php

/*
 * Identitas yayasan untuk layout admin dan publik.
 * Sementara dari .env; dipindah ke tb_pengaturan saat modul Company Profile dikerjakan.
 */
return [
    'nama' => env('SEKOLAH_NAMA', 'Yayasan Puspita Bangsa'),
    'nama_singkat' => env('SEKOLAH_NAMA_SINGKAT', 'Puspita Bangsa'),
    'deskripsi' => env('SEKOLAH_DESKRIPSI', 'Sistem Akademik Sekolah Yayasan Puspita Bangsa (SMP dan SMK).'),
    'alamat' => env('SEKOLAH_ALAMAT'),
    'telepon' => env('SEKOLAH_TELEPON'),
    'email' => env('SEKOLAH_EMAIL'),

    // Akun Super Administrator awal (dipakai SuperAdminSeeder); isi di .env, jangan di kode.
    'admin_awal' => [
        'nama' => env('ADMIN_NAMA', 'Super Administrator'),
        'username' => env('ADMIN_USERNAME'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
