<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Support\Izin;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case PemilikYayasan = 'pemilik_yayasan';
    case KepalaSekolah = 'kepala_sekolah';
    case WakilKepalaSekolah = 'wakil_kepala_sekolah';
    case WaliKelas = 'wali_kelas';
    case GuruMapel = 'guru_mapel';
    case GuruPiket = 'guru_piket';
    case PetugasTu = 'petugas_tu';
    case Pendaftar = 'pendaftar';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::PemilikYayasan => 'Pemilik Yayasan',
            self::KepalaSekolah => 'Kepala Sekolah',
            self::WakilKepalaSekolah => 'Wakil Kepala Sekolah',
            self::WaliKelas => 'Wali Kelas',
            self::GuruMapel => 'Guru Mata Pelajaran',
            self::GuruPiket => 'Guru Piket',
            self::PetugasTu => 'Petugas TU',
            self::Pendaftar => 'Pendaftar PPDB',
        };
    }

    /**
     * Permission bawaan tiap role. super_admin mendapat semuanya (dan juga lolos lewat Gate::before);
     * pendaftar tidak punya permission panel admin. Aturan "hanya data unit/kelas sendiri" ditegakkan
     * oleh global scope dan Policy, bukan di sini.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        $lihatSaja = [Izin::DASHBOARD_VIEW, ...Izin::lihat('pegawai', 'siswa', 'kelas', 'ppdb')];

        return match ($this) {
            self::SuperAdmin => Izin::semua(),
            self::PemilikYayasan, self::KepalaSekolah, self::WakilKepalaSekolah => $lihatSaja,
            self::PetugasTu => [
                Izin::DASHBOARD_VIEW,
                ...Izin::crud('ppdb'),
                ...Izin::crud('siswa'),
                ...Izin::crud('pegawai'),
                ...Izin::crud('berita'),
                // Akun pegawai dibuat/dikelola TU (CLAUDE.md §5); mengubah role tetap hanya super_admin.
                'pengguna.view', 'pengguna.create', 'pengguna.update',
            ],
            self::WaliKelas => [Izin::DASHBOARD_VIEW, 'siswa.view'],
            self::GuruMapel, self::GuruPiket => [Izin::DASHBOARD_VIEW],
            self::Pendaftar => [],
        };
    }
}
