<?php

use App\Support\NomorHp;

it('menyeragamkan nomor HP ke format lokal 08xx', function (string $input, string $hasil) {
    expect(NomorHp::normalisasi($input))->toBe($hasil);
})->with([
    ['081234567890', '081234567890'],
    ['+62 812-3456-7890', '081234567890'],
    ['6281234567890', '081234567890'],
    ['812 3456 7890', '081234567890'],
    ['(0812) 3456-7890', '081234567890'],
    ['', ''],
]);
