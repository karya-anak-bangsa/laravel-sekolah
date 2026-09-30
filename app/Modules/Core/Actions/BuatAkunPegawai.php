<?php

namespace App\Modules\Core\Actions;

use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Support\PasswordAcak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuatAkunPegawai
{
    /**
     * Membuat akun login untuk pegawai dengan kata sandi sementara (wajib diganti saat login pertama).
     * Username dibuat otomatis dari nama bila tidak diberikan.
     *
     * @param  list<string>  $roles
     * @return array{user: User, password: string}
     */
    public function __invoke(Pegawai $pegawai, ?string $username, array $roles = []): array
    {
        $password = PasswordAcak::buat();

        $user = DB::transaction(function () use ($pegawai, $username, $roles, $password) {
            $user = User::create([
                'id_pegawai' => $pegawai->getKey(),
                'nama' => $pegawai->nama_pegawai,
                'username' => $username ?: $this->usernameDariNama($pegawai->nama_pegawai),
                'password' => $password,
                'wajib_ganti_password' => true,
            ]);

            if ($roles !== []) {
                $user->syncRoles($roles);
            }

            return $user;
        });

        return ['user' => $user, 'password' => $password];
    }

    /** "Budi Santoso, S.Pd" -> "budi.santoso.s.pd"; tambah angka bila sudah dipakai (termasuk akun nonaktif). */
    private function usernameDariNama(string $nama): string
    {
        $dasar = Str::limit(Str::slug($nama, '.'), 40, '') ?: 'pegawai';
        $kandidat = $dasar;

        for ($i = 2; User::withTrashed()->where('username', $kandidat)->exists(); $i++) {
            $kandidat = $dasar.$i;
        }

        return $kandidat;
    }
}
