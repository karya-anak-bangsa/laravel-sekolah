<?php

use App\Support\PasswordAcak;

it('membuat kata sandi sepanjang yang diminta dengan huruf dan angka', function () {
    foreach (range(1, 50) as $i) {
        $password = PasswordAcak::buat();

        expect($password)->toHaveLength(10)
            ->toMatch('/[A-Za-z]/')
            ->toMatch('/[0-9]/');
    }

    expect(PasswordAcak::buat(16))->toHaveLength(16);
});

it('tidak memakai karakter yang mudah tertukar', function () {
    foreach (range(1, 100) as $i) {
        expect(PasswordAcak::buat(20))->not->toMatch('/[0O1lI]/');
    }
});

it('menghasilkan nilai yang berbeda pada setiap pemanggilan', function () {
    $hasil = collect(range(1, 30))->map(fn () => PasswordAcak::buat())->unique();

    expect($hasil)->toHaveCount(30);
});
