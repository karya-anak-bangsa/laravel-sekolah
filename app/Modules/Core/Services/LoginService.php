<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Enums\LoginArea;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public const MAKS_PERCOBAAN = 5;

    public const DETIK_TUNGGU = 60;

    private static ?string $hashPalsu = null;

    /**
     * Memverifikasi kredensial pada pintu masuk tertentu, lalu menyimpan sesi login.
     * Pemanggil bertanggung jawab me-regenerate sesi.
     *
     * @throws ValidationException bila dibatasi (429) atau kredensial/tipe akun tidak cocok.
     */
    public function masuk(LoginArea $area, string $identitas, string $password, bool $ingat, string $ip): User
    {
        $identitas = $area->normalisasi($identitas);
        $kunci = $this->kunciBatas($area, $identitas, $ip);
        $kolom = $area->field();

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            throw ValidationException::withMessages([
                $kolom => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.',
            ])->status(429);
        }

        $user = User::query()->where($kolom, $identitas)->first();

        // Selalu lakukan Hash::check agar waktu respons tidak membedakan akun yang ada dan tidak.
        $passwordCocok = Hash::check($password, $user?->password ?? $this->hashPalsu());

        if ($user === null || ! $passwordCocok || ! $area->mengizinkan($user)) {
            RateLimiter::hit($kunci, self::DETIK_TUNGGU);

            throw ValidationException::withMessages([$kolom => $area->pesanGagal()]);
        }

        RateLimiter::clear($kunci);
        Auth::login($user, $ingat);

        return $user;
    }

    private function kunciBatas(LoginArea $area, string $identitas, string $ip): string
    {
        return $area->value.'|'.Str::transliterate($identitas).'|'.$ip;
    }

    private function hashPalsu(): string
    {
        return self::$hashPalsu ??= Hash::make(Str::random(16));
    }
}
