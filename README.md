# Sistem Akademik Sekolah (SAS) — Yayasan Puspita Bangsa

Aplikasi Laravel 13 untuk dua unit sekolah (SMP dan SMK): master data kepegawaian dan kesiswaan,
company profile publik, dan PPDB. Aturan kerja, arsitektur, dan keputusan proyek ada di
[CLAUDE.md](CLAUDE.md); keputusan sementara di [docs/asumsi.md](docs/asumsi.md).

## Kebutuhan
- PHP ≥ 8.3 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `intl`, `fileinfo`, `zip`, `gd`, `curl`)
- Composer 2, Node.js ≥ 20 dan npm
- MySQL 8 / MariaDB (charset `utf8mb4`, collation `utf8mb4_unicode_ci`)

## Instalasi dari nol (lokal, Laragon)
```bash
git clone <url-repo> laravel-sekolah && cd laravel-sekolah
composer install
npm install
cp .env.example .env
php artisan key:generate
```
1. Buat dua database kosong: `db_sekolah` (kerja) dan `db_sekolah_test` (khusus test).
   ```sql
   CREATE DATABASE db_sekolah      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE db_sekolah_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Sesuaikan `.env` bila perlu (`DB_*`, `APP_URL`). Isi `ADMIN_USERNAME` dan `ADMIN_PASSWORD` untuk akun
   Super Administrator awal (pakai kata sandi kuat; jangan di-commit).
3. Migrasi dan isi data dasar (role, permission, Super Administrator, unit SMP/SMK, jurusan, tahun ajaran):
   ```bash
   php artisan migrate --seed
   ```
4. Jalankan:
   ```bash
   npm run dev          # Vite (admin + publik); untuk produksi: npm run build
   ```
   Buka `http://laravel-sekolah.test` (situs publik) dan `http://laravel-sekolah.test/admin/login` (panel admin).

## Perintah umum
| Perintah | Fungsi |
|----------|--------|
| `php artisan test` | Menjalankan seluruh test (Pest, memakai `db_sekolah_test`) |
| `./vendor/bin/pint` | Merapikan kode (wajib bersih sebelum commit) |
| `npm run build` | Membangun aset untuk produksi |
| `php artisan db:seed` | Menyelaraskan role/permission dan data dasar (aman dijalankan ulang) |

Test menolak berjalan bila `DB_DATABASE` tidak berakhiran `_test`, sehingga database kerja tidak ikut terhapus.

## Struktur
```
app/Modules/{Core,Kepegawaian,Kesiswaan,CompanyProfile,Ppdb}/   # modular monolith; tiap modul punya provider sendiri
app/Support/                                                   # helper lintas modul (ModuleServiceProvider, Menu, NomorHp)
config/menu.php                                                # menu admin/publik (disaring per route dan permission)
resources/views/{layouts,components}/                          # layout admin (Gentelella) dan publik (UniPulse)
resources/templates/unipulse/                                  # template asli UniPulse (referensi, tidak diedit)
```
Panel admin memakai Gentelella v4 (npm, tanpa Bootstrap/jQuery); situs publik memakai UniPulse (Bootstrap 5).

## Login
| Pengguna | Halaman | Kredensial |
|----------|---------|-----------|
| Pegawai (semua role selain pendaftar) | `/admin/login` | username + kata sandi |
| Pendaftar PPDB | `/ppdb/login` | nomor HP + kata sandi |

Lupa kata sandi ditangani petugas TU (tidak ada email/WA otomatis).

## Deploy (hosting Niagahoster, via SSH)
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --force      # hanya seeder yang idempotent; isi ADMIN_* di .env server untuk akun awal
php artisan optimize
php artisan storage:link         # sekali saja
```
Document root diarahkan ke folder `public/`; `APP_ENV=production`, `APP_DEBUG=false`. Jadwalkan cron
`* * * * * php artisan schedule:run` untuk scheduler dan queue (driver `database`).
