<?php

namespace App\Support;

/**
 * Untuk backed enum bertipe string yang punya method label(): menyediakan daftar pilihan
 * [nilai => label] untuk <select> dan untuk validasi.
 */
trait Opsi
{
    /** @return array<string, string> */
    public static function opsi(): array
    {
        $hasil = [];

        foreach (self::cases() as $kasus) {
            $hasil[$kasus->value] = $kasus->label();
        }

        return $hasil;
    }
}
