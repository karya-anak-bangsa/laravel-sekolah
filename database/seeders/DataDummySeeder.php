<?php

namespace Database\Seeders;

use App\Modules\Core\Database\Seeders\RolePermissionSeeder;
use App\Modules\Core\Database\Seeders\TahunAjaranSeeder;
use App\Modules\Core\Database\Seeders\UnitSekolahSeeder;
use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Enums\Role;
use App\Modules\Core\Models\Kelas;
use App\Modules\Core\Models\TahunAjaran;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Core\Models\User;
use App\Modules\Kepegawaian\Models\Pegawai;
use App\Modules\Kesiswaan\Models\AnggotaKelas;
use App\Modules\Kesiswaan\Models\Siswa;
use App\Modules\Kesiswaan\Models\WaliSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy skala nyata (CLAUDE.md §1, §7): 84 pegawai (Faker id_ID) dengan akun dan role, 12 rombel SMP,
 * 24 rombel SMK, dan sekitar 1.150 siswa beserta orang tua. SEMUA DATA FIKTIF; tidak untuk produksi.
 * Semua akun dummy memakai kata sandi "password".
 *
 * Dijalankan otomatis oleh DatabaseSeeder hanya di environment lokal/testing, atau manual:
 *   php artisan db:seed --class=Database\\Seeders\\DataDummySeeder
 */
class DataDummySeeder extends Seeder
{
    /** @var array{guru: int, tu: int} Komposisi pegawai per unit; total dengan 1 pimpinan yayasan = 84. */
    private const PEGAWAI = [
        'smp' => ['guru' => 26, 'tu' => 3],
        'smk' => ['guru' => 51, 'tu' => 3],
    ];

    private const ROMAWI = [7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];

    private int $minSiswaPerKelas = 30;

    private int $maxSiswaPerKelas = 35;

    /** @var list<string> */
    private array $usernameTerpakai = [];

    /** Mengubah jumlah siswa per rombel (dipakai test agar cepat). */
    public function dengan(int $min, int $max): static
    {
        $this->minSiswaPerKelas = $min;
        $this->maxSiswaPerKelas = $max;

        return $this;
    }

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DataDummySeeder tidak boleh dijalankan di produksi.');

            return;
        }

        if (Siswa::withoutGlobalScopes()->exists() || Pegawai::withoutGlobalScopes()->exists()) {
            $this->command?->warn('Data pegawai/siswa sudah ada; data dummy dilewati agar tidak ganda.');

            return;
        }

        $this->call([RolePermissionSeeder::class, UnitSekolahSeeder::class, TahunAjaranSeeder::class]);

        DB::transaction(function () {
            $tahun = TahunAjaran::aktif()->firstOrFail();

            $this->buatPimpinanYayasan();

            foreach (UnitSekolah::orderBy('jenjang')->get() as $unit) {
                $guru = $this->buatPegawaiUnit($unit);
                $kelas = $this->buatKelas($unit, $tahun, $guru);
                $this->buatSiswa($unit, $kelas, $tahun);
            }
        });

        $this->command?->info(sprintf(
            'Data dummy: %d pegawai, %d kelas, %d siswa.',
            Pegawai::withoutGlobalScopes()->count(),
            Kelas::withoutGlobalScopes()->count(),
            Siswa::withoutGlobalScopes()->count(),
        ));
    }

    private function buatPimpinanYayasan(): void
    {
        $pegawai = Pegawai::factory()->pimpinanYayasan()->create(['nama_pegawai' => 'Drs. H. Ahmad Fauzi (Ketua Yayasan)']);
        $this->buatAkun($pegawai, [Role::PemilikYayasan]);
    }

    /**
     * Guru pertama = kepala sekolah, dua berikutnya = wakil; semua guru = guru mapel; 3 guru = guru piket;
     * TU = petugas TU. Peran wali kelas diberikan saat kelas dibuat.
     *
     * @return list<Pegawai> guru unit, berurutan
     */
    private function buatPegawaiUnit(UnitSekolah $unit): array
    {
        $komposisi = self::PEGAWAI[$unit->jenjang->value];
        $guru = [];

        foreach (range(1, $komposisi['guru']) as $i) {
            $pegawai = Pegawai::factory()->guru()->diUnit($unit->id_unit_sekolah)->create();

            $roles = [Role::GuruMapel];

            if ($i === 1) {
                $roles[] = Role::KepalaSekolah;
            }

            if (in_array($i, [2, 3], true)) {
                $roles[] = Role::WakilKepalaSekolah;
            }

            if ($i > $komposisi['guru'] - 3) {
                $roles[] = Role::GuruPiket;
            }

            $this->buatAkun($pegawai, $roles);
            $guru[] = $pegawai;
        }

        foreach (range(1, $komposisi['tu']) as $i) {
            $this->buatAkun(Pegawai::factory()->tu()->diUnit($unit->id_unit_sekolah)->create(), [Role::PetugasTu]);
        }

        return $guru;
    }

    /**
     * SMP: tingkat 7–9 × 4 rombel. SMK: tingkat 10–12 × tiap jurusan × 2 rombel.
     *
     * @param  list<Pegawai>  $guru
     * @return list<Kelas>
     */
    private function buatKelas(UnitSekolah $unit, TahunAjaran $tahun, array $guru): array
    {
        $rombel = [];

        if ($unit->jenjang === Jenjang::Smp) {
            foreach ([7, 8, 9] as $tingkat) {
                foreach (['A', 'B', 'C', 'D'] as $huruf) {
                    $rombel[] = [$tingkat, null, self::ROMAWI[$tingkat].'-'.$huruf];
                }
            }
        } else {
            foreach ([10, 11, 12] as $tingkat) {
                foreach ($unit->jurusan()->orderBy('id_jurusan')->get() as $jurusan) {
                    foreach ([1, 2] as $nomor) {
                        $rombel[] = [$tingkat, $jurusan, self::ROMAWI[$tingkat].' '.$jurusan->kode_jurusan.' '.$nomor];
                    }
                }
            }
        }

        $hasil = [];
        $indeksWali = 3; // lewati kepala sekolah dan dua wakil

        foreach ($rombel as [$tingkat, $jurusan, $nama]) {
            $wali = $guru[$indeksWali++];
            $wali->user->assignRole(Role::WaliKelas->value);

            $hasil[] = Kelas::create([
                'id_unit_sekolah' => $unit->id_unit_sekolah,
                'id_jurusan' => $jurusan?->id_jurusan,
                'id_tahun_ajaran' => $tahun->id_tahun_ajaran,
                'tingkat' => $tingkat,
                'nama_kelas' => $nama,
                'id_pegawai_wali_kelas' => $wali->id_pegawai,
            ]);
        }

        return $hasil;
    }

    /** @param list<Kelas> $kelas */
    private function buatSiswa(UnitSekolah $unit, array $kelas, TahunAjaran $tahun): void
    {
        /** @var list<array{WaliSiswa, WaliSiswa}> $pasanganOrtu */
        $pasanganOrtu = [];
        $tahunMulai = (int) substr($tahun->nama_tahun_ajaran, 0, 4);

        foreach ($kelas as $k) {
            // Usia menurut tingkat: kelas 7 ≈ 12–13 tahun, kelas 10 ≈ 15–16 tahun.
            $usiaMin = $k->tingkat + 5;
            $usiaMaks = $k->tingkat + 6;

            foreach (range(1, random_int($this->minSiswaPerKelas, $this->maxSiswaPerKelas)) as $_) {
                $siswa = Siswa::factory()->create([
                    'id_unit_sekolah' => $unit->id_unit_sekolah,
                    'tanggal_lahir' => fake()->dateTimeBetween(($tahunMulai - $usiaMaks - 1).'-07-01', ($tahunMulai - $usiaMin).'-06-30')->format('Y-m-d'),
                ]);

                // ±6% siswa adalah adik dari siswa lain dan berbagi orang tua yang sama.
                if ($pasanganOrtu !== [] && random_int(1, 100) <= 6) {
                    [$ayah, $ibu] = $pasanganOrtu[array_rand($pasanganOrtu)];
                } else {
                    $ayah = WaliSiswa::factory()->laki()->create();
                    $ibu = WaliSiswa::factory()->perempuan()->create();
                    $pasanganOrtu[] = [$ayah, $ibu];
                }

                $siswa->wali()->attach($ayah->getKey(), ['hubungan' => 'ayah']);
                $siswa->wali()->attach($ibu->getKey(), ['hubungan' => 'ibu']);

                AnggotaKelas::create(['id_siswa' => $siswa->getKey(), 'id_kelas' => $k->getKey()]);
            }
        }
    }

    /** @param list<Role> $roles */
    private function buatAkun(Pegawai $pegawai, array $roles): User
    {
        $user = User::factory()->create([
            'id_pegawai' => $pegawai->id_pegawai,
            'nama' => $pegawai->nama_pegawai,
            'username' => $this->usernameUnik($pegawai->nama_pegawai),
        ]);
        $user->assignRole(array_map(fn (Role $r) => $r->value, $roles));

        $pegawai->setRelation('user', $user);

        return $user;
    }

    private function usernameUnik(string $nama): string
    {
        $dasar = Str::limit(Str::slug(preg_replace('/\(.*?\)|\b(drs|dra|h|hj|s|m|pd|kom|si|se|mm|mpd)\b\.?/i', '', $nama), '.'), 30, '') ?: 'pegawai';
        $kandidat = $dasar;

        for ($i = 2; in_array($kandidat, $this->usernameTerpakai, true); $i++) {
            $kandidat = $dasar.$i;
        }

        return $this->usernameTerpakai[] = $kandidat;
    }
}
