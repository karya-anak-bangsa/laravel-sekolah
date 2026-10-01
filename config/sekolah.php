<?php

/*
 * Nilai bawaan identitas yayasan (dari .env). Yang dipakai layout adalah situs('kunci'): nilai dari
 * tb_pengaturan (Pengaturan Situs di panel admin), dan memakai nilai di sini selama belum disimpan.
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
