<?php

namespace App\Support;

/** Kata sandi sementara yang mudah dibaca dan diketik (tanpa karakter mirip: 0/O, 1/l/I). */
final class PasswordAcak
{
    private const HURUF = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    private const ANGKA = '23456789';

    public static function buat(int $panjang = 10): string
    {
        $semua = self::HURUF.self::ANGKA;

        // Pastikan ada minimal satu huruf dan satu angka, lalu acak urutannya.
        $karakter = [self::ambil(self::HURUF), self::ambil(self::ANGKA)];

        while (count($karakter) < $panjang) {
            $karakter[] = self::ambil($semua);
        }

        for ($i = count($karakter) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$karakter[$i], $karakter[$j]] = [$karakter[$j], $karakter[$i]];
        }

        return implode('', $karakter);
    }

    private static function ambil(string $himpunan): string
    {
        return $himpunan[random_int(0, strlen($himpunan) - 1)];
    }
}
