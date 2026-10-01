<?php

namespace App\Modules\CompanyProfile\Support;

/**
 * Daftar kunci pengaturan situs, beserta label, kelompok, jenis isian, aturan validasi, dan nilai bawaan.
 * Nilai bawaan dipakai selama kunci belum disimpan di tb_pengaturan, sehingga situs tetap tampil utuh
 * sejak instalasi. Teks bawaan adalah contoh (dummy) yang harus diganti lewat panel admin.
 */
final class KatalogPengaturan
{
    public const KELOMPOK = [
        'identitas' => 'Identitas & Beranda',
        'kontak' => 'Kontak & Lokasi',
        'profil' => 'Profil Yayasan',
    ];

    /**
     * @return array<string, array{kelompok: string, label: string, jenis: string, aturan: list<string>, bantuan: ?string, bawaan: ?string}>
     */
    public static function semua(): array
    {
        return [
            'nama' => self::isian('identitas', 'Nama lengkap', 'text', ['required', 'max:150'], config('sekolah.nama')),
            'nama_singkat' => self::isian('identitas', 'Nama singkat', 'text', ['required', 'max:50'], config('sekolah.nama_singkat'), 'Tampil di header dan judul tab browser.'),
            'deskripsi' => self::isian('identitas', 'Deskripsi singkat', 'textarea', ['nullable', 'max:300'], config('sekolah.deskripsi'), 'Tampil di footer dan sebagai deskripsi mesin pencari.'),
            'hero_judul' => self::isian('identitas', 'Judul beranda', 'text', ['nullable', 'max:150'], 'Mendidik Generasi Berakhlak, Terampil, dan Berprestasi'),
            'hero_teks' => self::isian('identitas', 'Teks pembuka beranda', 'textarea', ['nullable', 'max:400'], 'Yayasan Puspita Bangsa menaungi SMP dan SMK yang berkomitmen menyiapkan peserta didik untuk melanjutkan pendidikan maupun memasuki dunia kerja.'),

            'alamat' => self::isian('kontak', 'Alamat', 'textarea', ['nullable', 'max:300'], config('sekolah.alamat') ?? 'Jl. Pendidikan No. 123, Kecamatan Contoh, Kota Contoh 12345'),
            'telepon' => self::isian('kontak', 'Telepon', 'text', ['nullable', 'max:30'], config('sekolah.telepon') ?? '(021) 5550-1234'),
            'email' => self::isian('kontak', 'Email', 'email', ['nullable', 'email', 'max:100'], config('sekolah.email') ?? 'info@puspitabangsa.example'),
            'jam_layanan' => self::isian('kontak', 'Jam layanan', 'text', ['nullable', 'max:100'], 'Senin - Jumat, 07.00 - 15.00 WIB'),
            'peta_embed_url' => self::isian('kontak', 'Alamat peta (embed)', 'url', ['nullable', 'url:https', 'starts_with:https://www.google.com/maps/embed', 'max:1000'], null, 'Google Maps → Bagikan → Sematkan peta → salin alamat di dalam src="...". Kosongkan bila tidak ingin menampilkan peta.'),
            'instagram' => self::isian('kontak', 'Instagram', 'url', ['nullable', 'url:https', 'max:200'], null, 'Alamat lengkap, mis. https://instagram.com/namaakun'),
            'facebook' => self::isian('kontak', 'Facebook', 'url', ['nullable', 'url:https', 'max:200'], null),
            'youtube' => self::isian('kontak', 'YouTube', 'url', ['nullable', 'url:https', 'max:200'], null),

            'sejarah' => self::isian('profil', 'Sejarah', 'textarea', ['nullable', 'max:5000'], self::sejarahBawaan(), 'Pisahkan paragraf dengan baris kosong.'),
            'visi' => self::isian('profil', 'Visi', 'textarea', ['nullable', 'max:1000'], 'Menjadi yayasan pendidikan yang melahirkan generasi beriman, berilmu, terampil, dan berdaya saing.'),
            'misi' => self::isian('profil', 'Misi', 'textarea', ['nullable', 'max:2000'], self::misiBawaan(), 'Satu butir misi per baris.'),
        ];
    }

    /** @return list<string> */
    public static function kunci(): array
    {
        return array_keys(self::semua());
    }

    /** @return array<string, array<string, array<string, mixed>>> definisi dikelompokkan per kelompok. */
    public static function perKelompok(): array
    {
        $hasil = [];

        foreach (self::semua() as $kunci => $definisi) {
            $hasil[$definisi['kelompok']][$kunci] = $definisi;
        }

        return $hasil;
    }

    private static function isian(string $kelompok, string $label, string $jenis, array $aturan, ?string $bawaan, ?string $bantuan = null): array
    {
        return compact('kelompok', 'label', 'jenis', 'aturan', 'bantuan', 'bawaan');
    }

    private static function sejarahBawaan(): string
    {
        return "Yayasan Puspita Bangsa didirikan atas kepedulian para pendiri terhadap pemerataan pendidikan yang bermutu bagi masyarakat sekitar. Berawal dari sebuah sekolah menengah pertama yang sederhana, yayasan terus berkembang seiring meningkatnya kepercayaan orang tua dan masyarakat.\n\n"
            ."Untuk menjawab kebutuhan dunia kerja, yayasan kemudian membuka sekolah menengah kejuruan dengan program keahlian Pariwisata, Bisnis Manajemen, Teknik Komputer dan Jaringan, serta Rekayasa Perangkat Lunak.\n\n"
            .'Hingga kini, SMP dan SMK Puspita Bangsa terus berbenah, baik pada sarana, kurikulum, maupun tata kelola, dengan tetap berpegang pada nilai-nilai yang dipegang sejak awal: disiplin, kejujuran, dan kepedulian.';
    }

    private static function misiBawaan(): string
    {
        return "Menyelenggarakan pembelajaran yang aktif, menyenangkan, dan berorientasi pada karakter.\n"
            ."Membekali peserta didik dengan keterampilan yang sesuai kebutuhan dunia usaha dan dunia industri.\n"
            ."Menumbuhkan kebiasaan beribadah, berdisiplin, dan berperilaku santun.\n"
            ."Membangun kerja sama dengan orang tua, masyarakat, dan dunia usaha.\n"
            .'Mengelola sekolah secara transparan, akuntabel, dan berkelanjutan.';
    }
}
