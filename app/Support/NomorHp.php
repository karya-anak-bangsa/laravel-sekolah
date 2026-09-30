<?php

namespace App\Support;

/** Penyeragaman nomor HP Indonesia ke format lokal "08xxxxxxxxxx" (dipakai saat registrasi dan login pendaftar). */
final class NomorHp
{
    public static function normalisasi(string $nomor): string
    {
        $digit = preg_replace('/\D+/', '', $nomor) ?? '';

        return match (true) {
            str_starts_with($digit, '62') => '0'.substr($digit, 2),
            str_starts_with($digit, '8') => '0'.$digit,
            default => $digit,
        };
    }
}
