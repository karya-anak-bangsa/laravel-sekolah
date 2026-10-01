# CLAUDE.md — Sistem Akademik Sekolah (SAS) Yayasan Puspita Bangsa

File ini dibaca Claude Code di awal setiap sesi. Isinya aturan kerja, arsitektur, dan keputusan yang
sudah disepakati. Detail per modul ada di folder `docs/` dan dibaca hanya saat modul itu dikerjakan.

## 1. Ringkasan Project

- **Klien:** Yayasan Puspita Bangsa, dua unit sekolah: **SMP** dan **SMK**.
- **Developer:** satu orang (fullstack). Kontrak **sewa** — developer tetap mengelola dan memelihara
  sistem setelah go-live, sehingga kemudahan pemeliharaan jangka panjang adalah prioritas.
- **Target:** mulai 1 Oktober 2026, siap dipakai awal semester genap (Januari 2027).
- **Pendekatan:** bertahap. Informasi dari yayasan masih terbatas, jadi tabel yang belum punya data
  acuan dibuat **sederhana dulu** (hanya kolom inti) dan diperluas kemudian lewat migration baru.

### Skala data (untuk seeder, index, dan paginasi)
- Pegawai: 84 orang.
- SMP: kelas VII–IX, 4 rombel per tingkat (12 rombel), 30–35 siswa per rombel.
- SMK: jurusan Pariwisata, Bisnis Manajemen, TKJ, RPL; kelas X–XII, 2 rombel per jurusan per tingkat
  (24 rombel), 30–35 siswa per rombel.
- Total siswa aktif sekitar 1.100–1.300.

### Modul
| # | Modul | Status |
|---|-------|--------|
| 0 | Core: master data **kepegawaian** dan **siswa & wali siswa**, unit sekolah, tahun ajaran, kelas, auth, role | Dikerjakan |
| 1 | Company Profile (publik) | Dikerjakan — konten sudah tersedia dari developer |
| 2 | PPDB / Penerimaan Siswa Baru | Dikerjakan — lihat `docs/ppdb-formulir.md` |
| – | Absensi Siswa, Tabungan Siswa, Rapor | **Ditunda.** Jangan dikerjakan atau dirancang sampai ada instruksi |
| – | Absensi guru, CBT, pembayaran SPP, portal orang tua | Rencana masa depan — arsitektur harus siap menampungnya |

## 2. Tech Stack & Lingkungan

- **Laravel 13**, **PHP ≥ 8.3** (lokal di Laragon dan di hosting).
- **Database:** MySQL/MariaDB. Charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- **Dev:** Windows + Laragon + VSCode + Claude Code. URL lokal `http://laravel-sekolah.test`.
- **Produksi:** hosting Niagahoster paket unlimited, **mendukung Node.js** (domain & hosting milik developer).
- **Build aset:** Vite. **Test:** Pest. **Format kode:** Laravel Pint.
- **Timezone** `Asia/Jakarta`, **locale** `id`. Tampilan tanggal `d/m/Y`, uang `Rp 1.500.000`.

### Template (dua template berbeda — jangan dicampur)
| Area | Template | Pengguna |
|------|----------|----------|
| Panel internal `/admin` | **Gentelella v4** (Colorlib, MIT). Vanilla JS + SCSS + Vite, **tanpa Bootstrap dan tanpa jQuery**. Chart ECharts, tabel DataTables. | Pemilik yayasan, kepala sekolah, wakil kepala sekolah, wali kelas, guru mapel, guru piket, petugas TU |
| Situs publik `/` | **UniPulse** (BootstrapMade, berlisensi). Bootstrap 5. | Masyarakat umum (company profile) dan akun pendaftar PPDB (registrasi, isi formulir, pantau status) |

Aturan:
- Dua entry point Vite terpisah: `resources/js/admin.js` + `resources/scss/admin.scss`, dan
  `resources/js/public.js` + `resources/scss/public.scss`. Bootstrap tidak boleh termuat di panel admin.
- Jangan menambahkan jQuery ke panel admin.
- **Gentelella** dipasang lewat npm (`gentelella`, tanpa salinan di repo; referensinya ada di
  `node_modules/gentelella/production/`). `admin.js` hanya mengimpor `gentelella/v4/shell` dan
  `admin.scss` meng-`@use` `gentelella/scss/v4/main`; sidebar/topbar dirender Blade, bukan JS template.
- File asli **UniPulse** disimpan di `resources/templates/unipulse/` sebagai referensi dan **tidak diedit**;
  `public.scss` meng-`@import` SCSS aslinya, dan pustakanya (bootstrap, aos, swiper, dst.) dari npm.
- HTML template dipecah menjadi layout dan komponen Blade:
  `layouts/admin.blade.php`, `layouts/admin-auth.blade.php`, `layouts/public.blade.php`, `<x-admin.card>`,
  `<x-admin.table>`, `<x-form.input theme="admin|public">`, dst. Menu di `config/menu.php` (disaring
  per route dan permission oleh `App\Support\Menu`).
- Pint mengecualikan folder template (`pint.json`).
- File lisensi UniPulse tidak di-commit.

## 3. Konvensi Database (TIDAK mengikuti konvensi Laravel)

Ini keputusan tetap. Selain database, semua hal lain mengikuti konvensi Laravel.

| Aturan | Contoh |
|--------|--------|
| Nama tabel: prefix `tb_` + kata benda tunggal snake_case | `tb_siswa`, `tb_pegawai`, `tb_wali_siswa`, `tb_kelas` |
| Primary key: `id_` + nama tabel tanpa prefix, bigint auto increment | `id_siswa`, `id_pegawai` |
| Foreign key: nama kolom **sama dengan PK tabel yang dirujuk** | `tb_kelas.id_pegawai` → `tb_pegawai.id_pegawai` |
| FK dengan makna khusus: tambahkan akhiran peran | `id_pegawai_wali_kelas`, `id_pegawai_verifikator` |
| Tabel pivot: `tb_` + dua entitas | `tb_siswa_wali` |
| Timestamps & soft delete tetap bawaan Laravel | `created_at`, `updated_at`, `deleted_at` |

Tabel yang boleh memakai nama bawaan (infrastruktur framework/paket, bukan data domain):
`migrations`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`,
`password_reset_tokens`, tabel spatie/laravel-permission, dan `activity_log`.
Tabel pengguna login tetap mengikuti aturan domain: **`tb_user`** dengan PK `id_user`.

Konsekuensi yang wajib diterapkan di kode:
```php
// Model — selalu tetapkan tabel dan primary key secara eksplisit
#[Table('tb_siswa', key: 'id_siswa')]
class Siswa extends Model { /* ... */ }

// Migration
Schema::create('tb_kelas', function (Blueprint $table) {
    $table->id('id_kelas');
    $table->foreignId('id_unit_sekolah')->constrained('tb_unit_sekolah', 'id_unit_sekolah');
    $table->foreignId('id_pegawai_wali_kelas')->nullable()->constrained('tb_pegawai', 'id_pegawai');
    $table->timestamps();
});

// Relasi — selalu tulis foreign key dan owner/local key secara eksplisit
public function kelas(): BelongsTo
{
    return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
}
```
- `config/auth.php` memakai model `User` dengan `#[Table('tb_user', key: 'id_user')]`.
  Sesuaikan konfigurasi spatie/laravel-permission (`model_morph_key`) bila perlu.
- Route model binding tetap berjalan karena memakai primary key model.
- Validasi `exists`/`unique` wajib menyebut tabel dan kolom: `exists:tb_kelas,id_kelas`.
- Nilai pilihan (agama, pendidikan, pekerjaan, dsb.) disimpan sebagai kode string dari PHP backed enum.
- Uang disimpan sebagai integer rupiah (`unsignedBigInteger`), bukan float.

## 4. Arsitektur: Modular Monolith

```
app/Modules/
  Core/            # UnitSekolah, Jurusan, TahunAjaran, Kelas, User, role
  Kepegawaian/     # Master data pegawai (guru & TU)
  Kesiswaan/       # Master data siswa & wali siswa, anggota kelas
  CompanyProfile/  # Berita/pengumuman, galeri, prestasi, pengaturan situs
  Ppdb/            # Pendaftaran siswa baru
app/Support/       # Helper lintas modul
```
Struktur tiap modul seragam: `Models/ Enums/ Http/Controllers/ Http/Requests/ Services/ Actions/
Policies/ Events/ Listeners/ Database/Migrations/ Database/Seeders/ Database/Factories/ routes/web.php
resources/views/ Providers/<Nama>ServiceProvider.php`. Test di `tests/Feature/<Nama>/` dan `tests/Unit/<Nama>/`.

Aturan:
- Setiap modul punya ServiceProvider yang mendaftarkan routes, views (`ppdb::`, `kesiswaan::`, dst.), dan migrations.
- **Controller tipis.** Validasi di FormRequest, logika bisnis di Service/Action, otorisasi di Policy.
- **Dua master data menjadi pusat semua modul.** Modul transaksi (PPDB sekarang; absensi, tabungan,
  rapor, SPP, CBT nanti) hanya menyimpan foreign key ke `tb_pegawai` (siapa yang mencatat/bertugas)
  dan `tb_siswa` (siapa yang bersangkutan). Jangan menduplikasi nama/identitas di tabel transaksi.
- Modul tidak mengakses tabel modul lain secara langsung, kecuali tabel master (Core, Kepegawaian, Kesiswaan).
- Data terikat ke `id_unit_sekolah`. Pengguna tingkat unit hanya melihat data unitnya (global scope + Policy);
  pemilik yayasan melihat semua unit.
- Tidak memakai package pihak ketiga untuk sistem modul.

### Tabel awal (sederhana dulu, diperluas bila ada data acuan)
| Modul | Tabel | Kolom inti |
|-------|-------|-----------|
| Core | `tb_unit_sekolah` | id_unit_sekolah, nama_unit_sekolah, jenjang (smp/smk) |
| Core | `tb_jurusan` | id_jurusan, id_unit_sekolah, nama_jurusan, kode_jurusan |
| Core | `tb_tahun_ajaran` | id_tahun_ajaran, nama_tahun_ajaran (2026/2027), semester_aktif, is_aktif |
| Core | `tb_kelas` | id_kelas, id_unit_sekolah, id_jurusan (null utk SMP), id_tahun_ajaran, tingkat, nama_kelas, id_pegawai_wali_kelas |
| Core | `tb_user` | id_user, id_pegawai (nullable, terisi untuk pegawai), nama, username (unik, nullable — login pegawai), no_hp (unik, nullable — login pendaftar), email (nullable), password |
| Kepegawaian | `tb_pegawai` | id_pegawai, id_unit_sekolah (null = tingkat yayasan), nama_pegawai, jenis_pegawai (guru/tu/pimpinan_yayasan) |
| Kesiswaan | `tb_siswa` | id_siswa, id_unit_sekolah, status_siswa, + identitas dari formulir PPDB |
| Kesiswaan | `tb_wali_siswa` | id_wali_siswa, nama_wali_siswa, + data dari formulir PPDB |
| Kesiswaan | `tb_siswa_wali` | id_siswa, id_wali_siswa, hubungan (ayah/ibu/wali) |
| Kesiswaan | `tb_anggota_kelas` | id_anggota_kelas, id_siswa, id_kelas |
| PPDB | `tb_gelombang` | id_gelombang, id_tahun_ajaran, nama_gelombang, tanggal_mulai, tanggal_selesai, biaya_pendaftaran |
| PPDB | `tb_pendaftaran` | id_pendaftaran, no_pendaftaran, id_user (akun pendaftar), id_siswa, id_unit_sekolah, id_gelombang, id_jurusan (wajib utk SMK), status, status_pembayaran, id_pegawai_verifikator — rincian lengkap di `docs/ppdb-formulir.md` |
| Company Profile | `tb_pengaturan` (kunci, nilai), `tb_pengurus` (id_unit_sekolah null = yayasan, nama_pengurus, jabatan, urutan) | sudah dibuat (Fase 2A); `tb_berita` (Fase 2B), `tb_galeri` dan `tb_prestasi` (Fase 2C) sudah dibuat |

Catatan desain:
- Satu tabel `tb_pegawai` untuk guru dan TU (bukan `tb_guru` terpisah), karena kepala sekolah dan wakil
  juga guru, dan satu orang bisa punya beberapa tugas. **Jabatan/tugas bukan kolom**, melainkan role
  (lihat §5) atau penugasan (mis. `tb_kelas.id_pegawai_wali_kelas`).
- Wali siswa dipisah dari siswa dengan pivot `tb_siswa_wali`, agar kakak-adik bisa berbagi data orang tua
  (berguna untuk SPP dan portal orang tua di masa depan).
- Calon siswa langsung disimpan di `tb_siswa` dengan `status_siswa = calon`; setelah diterima menjadi
  `aktif`. Status lain: `lulus`, `pindah`, `keluar`.

## 5. Pengguna & Hak Akses

Semua 84 pegawai adalah pengguna panel admin. Role memakai spatie/laravel-permission; satu pegawai
boleh memiliki lebih dari satu role.

| Role | Cakupan |
|------|---------|
| `super_admin` | Developer. Semua akses termasuk pengaturan sistem |
| `pemilik_yayasan` | Melihat semua unit (dashboard, laporan) |
| `kepala_sekolah`, `wakil_kepala_sekolah` | Unit masing-masing |
| `wali_kelas` | Kelas yang diampu |
| `guru_mapel` | Disiapkan untuk modul nilai/rapor nanti |
| `guru_piket` | Disiapkan untuk modul absensi nanti |
| `petugas_tu` | Kelola PPDB (gelombang, pembayaran, verifikasi, penerimaan, reset password pendaftar), master data siswa & pegawai, berita/pengumuman. Membantu pendaftar yang datang ke sekolah lewat halaman PPDB publik |

Selain pegawai, ada role **`pendaftar`** untuk calon siswa/wali. Akun pendaftar hanya mengakses area
PPDB di situs publik (layout UniPulse) dan **ditolak dari `/admin`**. Satu akun boleh mendaftarkan lebih dari
satu anak (kakak-adik); akun ini kelak dapat dikembangkan menjadi akun portal orang tua.
Karena tidak ada notifikasi email/WA, lupa password ditangani petugas TU (fitur reset password di panel admin).

### Login
| Pengguna | Halaman login | Kredensial | Layout |
|----------|---------------|------------|--------|
| Pegawai (semua role selain `pendaftar`) | `/admin/login` | **username + password** | Gentelella |
| Pendaftar PPDB | `/ppdb/login` | **no HP + password** | UniPulse |

- Satu tabel `tb_user` dan satu guard `web`; perbedaannya hanya kolom identitas yang dicek di tiap halaman login.
- Login pegawai menolak akun `pendaftar`, dan login pendaftar menolak akun pegawai.
- Akun pegawai dibuat oleh `super_admin`/petugas TU dari data `tb_pegawai` (tanpa registrasi mandiri).
- Rate limit pada kedua halaman login.

Otorisasi selalu lewat Policy/Gate berbasis **permission** (mis. `ppdb.create`), bukan pengecekan nama role di kode.

## 6. Standar Kualitas ISO/IEC 9126

Checklist sebelum fitur dinyatakan selesai:

| Karakteristik | Penerapan |
|---------------|-----------|
| **Functionality** | Sesuai data acuan klien; validasi server-side lengkap; Policy di setiap aksi; CSRF, rate limit login; ekspor Excel/PDF bila dibutuhkan. |
| **Reliability** | Operasi multi-tabel dalam `DB::transaction()`; halaman error 403/404/419/500 berbahasa Indonesia; soft delete untuk data master; backup database terjadwal. |
| **Usability** | Bahasa Indonesia yang lugas; form panjang dibuat per langkah; pesan validasi di samping field; responsif di HP; komponen Blade konsisten. |
| **Efficiency** | Index pada foreign key dan kolom filter/pencarian; paginasi server-side; eager loading (tanpa N+1); cache untuk konten company profile; gambar dikompres. |
| **Maintainability** | Struktur modular; controller tipis; enum & konfigurasi terpusat; feature test tiap alur utama; keputusan dicatat di `docs/`. |
| **Portability** | Semua konfigurasi via `.env`; tanpa path Windows yang di-hardcode; README instalasi dari nol; berjalan di Laragon dan hosting Niagahoster. |

## 7. Data Dummy & Keamanan Berkas

- Selama pengembangan **seluruh data siswa, wali, dan pegawai adalah data dummy** (Faker `id_ID`),
  dibuat lewat factory/seeder dengan skala seperti §1.
- Jangan memasukkan data asli ke seeder, test, atau commit.
- PPDB tidak memakai unggahan berkas. Bila kelak ada fitur unggah dokumen siswa, simpan di disk privat dan
  akses lewat controller dengan otorisasi.
- Aset company profile (logo, foto kegiatan) boleh di disk `public`.

### Registrasi akun PPDB publik
- Rate limit pada registrasi dan login; honeypot pada form registrasi.
- Pendaftar hanya dapat melihat dan mengubah pendaftaran miliknya sendiri (Policy berbasis `id_user`).
- Cek duplikat NISN sebelum menyimpan; pesan error tidak boleh membocorkan data orang lain.

## 8. Deploy ke Hosting

- Document root domain harus mengarah ke folder `public/`.
- Hosting mendukung Node.js, jadi deploy dilakukan di server via SSH:
  ```bash
  git pull origin main
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  php artisan migrate --force
  php artisan optimize        # config, route, view, event cache
  php artisan storage:link    # sekali saja
  ```
- Pastikan versi PHP (≥ 8.3) dan Node.js di hosting sesuai dengan yang dipakai di lokal.
- Queue memakai driver `database`; scheduler dan pemrosesan queue via cron `php artisan schedule:run` tiap menit.
- `APP_ENV=production`, `APP_DEBUG=false` di produksi.

## 9. Aturan Kerja Claude Code

1. **Rencanakan dulu.** Untuk fitur baru, tulis rencana singkat (tabel, route, file) lalu tunggu persetujuan.
2. **Jangan mengarang aturan bisnis.** Bila informasi belum ada, buat versi paling sederhana, catat
   asumsinya di `docs/asumsi.md`, dan tanyakan.
3. **Jangan mengerjakan modul yang ditunda** (absensi, tabungan, rapor).
4. **Wajib test.** Setiap alur utama punya feature test, termasuk uji bahwa role lain ditolak.
5. Sebelum memasang package baru, cek kompatibilitas dengan Laravel 13 dan minta persetujuan.
6. Jangan mengubah migration yang sudah di-commit; buat migration baru.
7. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau perintah destruktif tanpa izin eksplisit.
8. Perbarui file ini atau `docs/` setelah ada keputusan penting.

## 10. Git

- Semua commit langsung ke **`main`** (developer tunggal).
- Karena tidak ada branch pengaman, **sebelum setiap commit wajib**: `php artisan test` hijau dan `./vendor/bin/pint` bersih.
- Satu commit = satu perubahan logis, format Conventional Commits:
  `feat(ppdb): tambah form pendaftaran`, `fix(kesiswaan): ...`, `test(...)`, `docs(...)`.
- Beri tag pada setiap rilis ke produksi: `v0.1.0`, `v0.2.0`, dst.
- `.env`, `vendor/`, `node_modules/`, `storage/*` (kecuali `.gitignore`), dan file lisensi template tidak di-commit.

## 11. Perintah Umum

```bash
composer install && npm install
php artisan migrate --seed      # hanya di lokal
npm run dev                     # Vite (admin + public)
npm run build                   # sebelum deploy
php artisan test
./vendor/bin/pint
```

## 12. Roadmap (Okt 2026 – Jan 2027)

| Fase | Perkiraan waktu | Isi |
|------|-----------------|-----|
| 0. Fondasi | Minggu 1–2 Okt | Instalasi, struktur modul, konvensi database, layout 2 template, auth, role & permission |
| 1. Master data | Minggu 3–4 Okt | Unit sekolah, jurusan, tahun ajaran, kelas, pegawai, siswa & wali siswa, seeder dummy |
| 2. Company Profile | Awal–pertengahan Nov | Halaman publik (UniPulse) + kelola berita/pengumuman oleh developer & petugas TU |
| 3. PPDB | Pertengahan Nov – awal Des | Gelombang, registrasi akun, formulir online bertahap + persetujuan pernyataan, pencatatan pembayaran, verifikasi TU, penempatan kelas, cetak formulir & pernyataan |
| 4. Deploy & pelatihan | Desember – libur semester | Deploy ke hosting, pelatihan pegawai, perbaikan |

## 13. Dokumen Pendukung

- `docs/ppdb-formulir.md` — rincian field formulir PPDB dan pemetaannya ke tabel.
- `docs/pertanyaan-klien.md` — jawaban yayasan dan pertanyaan yang masih terbuka.
- `docs/asumsi.md` — asumsi saat data acuan belum ada (dibuat bila perlu).
