# Asumsi Pengembangan

Catatan keputusan yang diambil saat data acuan dari yayasan belum ada. Tiap butir masih bisa diubah;
bila ada jawaban yayasan, perbarui di sini dan buat migration/kode baru bila perlu.

## Database (Fase 0)
- `tb_user.email` tidak unik (spesifikasi hanya menyebut nullable). `username` dan `no_hp` unik.
- Semua tabel master (`tb_unit_sekolah`, `tb_jurusan`, `tb_tahun_ajaran`, `tb_pegawai`, `tb_kelas`, `tb_user`) memakai soft delete.
- `tb_kelas.tingkat` berupa angka (7–9 untuk SMP, 10–12 untuk SMK). Belum ada unique constraint pada
  `kode_jurusan` dan `nama_kelas`; validasi duplikat dikerjakan di FormRequest saat CRUD dibuat.
- Hanya satu tahun ajaran boleh `is_aktif = true`; ditegakkan di Service saat CRUD dibuat (belum di database).
- Tabel `password_reset_tokens` tetap ada (infrastruktur Laravel), tetapi tidak dipakai: lupa password ditangani petugas TU.

## Autentikasi dan hak akses
- Super Administrator = role `super_admin` (akses penuh, termasuk lewat `Gate::before`); akun awal dibuat dari
  `ADMIN_USERNAME`/`ADMIN_PASSWORD` di `.env` dan tidak terhubung ke `tb_pegawai` (`id_pegawai` null).
- Nama permission: `<sumber-daya>.<aksi>` (`view`, `create`, `update`, `delete`) ditambah `dashboard.view` dan
  `pengguna.assign-role`. Matriks role ada di `App\Modules\Core\Enums\Role::permissions()`:
  - `pemilik_yayasan`, `kepala_sekolah`, `wakil_kepala_sekolah`: dashboard + lihat pegawai, siswa, kelas, PPDB.
  - `petugas_tu`: dashboard; kelola PPDB, siswa, pegawai, berita; lihat/buat/ubah pengguna (akun pegawai dibuat TU
    menurut §5). Mengubah role pengguna hanya `super_admin`.
  - `wali_kelas`: dashboard + lihat siswa. `guru_mapel`, `guru_piket`: dashboard saja.
  - `pendaftar`: tanpa permission panel admin.
- Pembatasan "hanya unit/kelas sendiri" memakai `UnitSekolahScope` (pegawai dengan `id_unit_sekolah` terisi).
  Pegawai tingkat yayasan dan akun tanpa pegawai melihat semua unit. Pembatasan wali kelas hanya pada kelas
  yang diampu dikerjakan di Policy saat modul siswa/kelas dibuat.
- Login: username tidak membedakan huruf besar/kecil. Nomor HP pendaftar dinormalisasi ke format `08xxxxxxxxxx`
  (menerima `+62`, `62`, spasi, dan strip) baik saat login maupun (nanti) registrasi.
- Rate limit login: 5 percobaan gagal per kombinasi area + identitas + IP, tunggu 60 detik. Semua penyebab gagal
  (akun tidak ada, password salah, tipe akun salah) memakai pesan yang sama.
- Setelah login pendaftar berhasil, sementara diarahkan ke beranda; area pendaftar dibuat di Fase 3.

## Master data Core (Fase 1A)
- Disetujui 2026-10-01: petugas TU mengelola kelas dan tahun ajaran (`kelas.*`, `tahun-ajaran.*`); unit dan jurusan
  hanya `super_admin`. Wali kelas memakai permission terpisah `siswa.view-kelas` (hanya kelas yang diampu).
- Nama kelas harus unik per unit + tahun ajaran (kelas yang dihapus tidak dihitung). Tingkat SMP 7–9, SMK 10–12.
- Jurusan wajib untuk kelas SMK dan dilarang untuk SMP; jurusan harus milik unit kelas itu.
- Wali kelas harus pegawai berjenis guru pada unit yang sama. Belum ada aturan "satu guru hanya satu kelas per tahun".
- Petugas TU tingkat unit hanya bisa membuat/melihat kelas di unitnya (scope + validasi unit).
- Tahun ajaran baru selalu berstatus tidak aktif; pengaktifan lewat tombol "Aktifkan" (atomik, satu aktif).
  Tahun ajaran aktif atau yang sudah punya kelas tidak bisa dihapus. Pergantian semester lewat ubah tahun ajaran.
- Unit dengan jurusan/kelas/pegawai, dan jurusan yang dipakai kelas, tidak bisa dihapus.
- Daftar memakai paginasi server-side (25 baris) dan filter lewat query string; DataTables client-side tidak dipakai.

## Data dasar (seeder)
- Nama unit "SMP Puspita Bangsa" dan "SMK Puspita Bangsa" adalah asumsi (belum ada nama resmi).
- Jurusan SMK: Pariwisata (`PAR`), Bisnis Manajemen (`BM`), Teknik Komputer dan Jaringan (`TKJ`),
  Rekayasa Perangkat Lunak (`RPL`). Kode `PAR` dan `BM` adalah asumsi; nama lengkap TKJ/RPL mengikuti lazimnya SMK.
- Tahun ajaran awal 2026/2027 semester ganjil dan aktif (sistem dipakai awal semester genap Januari 2027;
  ganti semester/tahun ajaran lewat CRUD tahun ajaran nanti).
- Seeder ini hanya berisi struktur organisasi, bukan data pribadi, sehingga aman dijalankan di produksi.
  Data dummy pegawai/siswa (Faker `id_ID`) dibuat di Fase 1 lewat seeder terpisah khusus lokal.

## Tampilan
- Identitas yayasan di layout dibaca dari `config/sekolah.php` (`.env`) sampai `tb_pengaturan` dibuat di modul Company Profile.
- Item menu di `config/menu.php` (nama route dan permission) adalah usulan; item baru tampil otomatis saat route-nya ada.
