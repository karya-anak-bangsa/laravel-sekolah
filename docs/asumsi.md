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

## Kepegawaian dan akun (Fase 1B)
- `tb_pegawai` tetap minimal (nama, unit, jenis); kolom tambahan (NIP, jenis kelamin, no HP, dst.) ditambah nanti
  lewat migration baru saat yayasan memberi data acuan (disetujui 2026-10-01).
- Pegawai tingkat yayasan (`pimpinan_yayasan`) tanpa unit; hanya pengguna tingkat yayasan yang boleh mencatatnya.
  Guru yang masih menjadi wali kelas tidak bisa dihapus atau diganti jenisnya.
- Menghapus pegawai = soft delete pegawai + menonaktifkan akun loginnya (soft delete `tb_user`).
- Akun dibuat dari pegawai yang belum punya akun: username otomatis `nama.dengan.titik` (ditambah angka bila dipakai),
  kata sandi sementara acak 10 karakter ditampilkan sekali, dan wajib diganti saat login pertama
  (`tb_user.wajib_ganti_password`). Reset password memakai mekanisme yang sama.
- **Petugas TU dapat membuat akun tetapi tidak memberi role** (hanya `super_admin` yang punya `pengguna.assign-role`),
  sehingga akun buatan TU baru berfungsi setelah super_admin menetapkan role. Belum ada role bawaan per jenis pegawai
  agar tidak mengarang aturan hak akses; bisa diubah bila yayasan menginginkannya.
- Menonaktifkan/mengaktifkan akun memakai permission `pengguna.delete` (hanya super_admin). TU boleh ubah username dan reset sandi
  akun pegawai di unitnya, kecuali akun super_admin. Akun sendiri tidak bisa dinonaktifkan, dan super_admin tidak bisa
  mencabut role super_admin dari dirinya sendiri.
- Kata sandi baru minimal 8 karakter, berisi huruf dan angka, serta berbeda dari yang lama.
- Akun Super Administrator awal dari seeder juga wajib mengganti kata sandi saat login pertama.

## Kesiswaan (Fase 1C)
- Tabel mengikuti `docs/ppdb-formulir.md`. Kolom identitas siswa dan wali dibuat nullable (selain nama dan unit) agar
  draf PPDB bisa disimpan; kelengkapan divalidasi di aplikasi. Form ubah data admin mewajibkan: nama, status,
  jenis kelamin, tempat dan tanggal lahir, agama, kebutuhan khusus, serta nama ayah dan ibu (wali opsional).
- NISN unik di antara siswa yang belum dihapus (kolom virtual `nisn_aktif`); siswa yang di-soft delete tidak menghalangi
  NISN dipakai lagi. `tb_siswa_wali` memakai primary key gabungan (id_siswa, hubungan): satu ayah, satu ibu, satu wali.
- Siswa baru hanya lewat PPDB; panel admin hanya menampilkan daftar, detail, dan mengubah data (tidak ada tombol tambah).
- Data orang tua yang dipakai lebih dari satu siswa (kakak-adik) diubah di tempat, sehingga berlaku untuk semuanya.
- Penempatan kelas: satu siswa hanya satu kelas per tahun ajaran (menempatkan ulang = memindahkan); kelas harus di unit
  yang sama; hanya status calon/aktif yang dapat ditempatkan. Kelas yang masih punya siswa tidak bisa dihapus.
  Penempatan tidak mengubah `status_siswa` (perubahan calon -> aktif adalah bagian penerimaan PPDB, Fase 3).
- Wali kelas (`siswa.view-kelas`) hanya melihat siswa di kelas yang ia ampu pada tahun ajaran aktif, dan daftar anggota
  kelas itu; hanya-lihat. Menu "Siswa" tampil bila pengguna punya `siswa.view` atau `siswa.view-kelas`.

## Data dummy (Fase 1D)
- `DataDummySeeder` hanya jalan di environment non-produksi dan dipanggil otomatis oleh `DatabaseSeeder` hanya bila `APP_ENV=local`.
  Dilewati bila sudah ada data pegawai/siswa. Semua akun dummy memakai kata sandi `password` dan tidak wajib ganti.
- Komposisi pegawai (84): 1 pimpinan yayasan, SMP 26 guru + 3 TU, SMK 51 guru + 3 TU. Per unit: guru ke-1 kepala sekolah,
  ke-2 dan ke-3 wakil, tiga guru terakhir guru piket; semua guru juga guru mapel; satu guru sebagai wali untuk tiap
  rombel. Angka ini hanya untuk skala uji, bukan data resmi yayasan.
- Kelas: SMP 7–9 × 4 rombel (VII-A…), SMK 10–12 × 4 jurusan × 2 rombel (mis. X RPL 1); 30–35 siswa per rombel;
  ±6% siswa dibuat sebagai adik yang berbagi orang tua.

## Company Profile (Fase 2A)
- Disetujui 2026-10-01: situs publik satu untuk seluruh yayasan (bagian SMP dan SMK di dalamnya); pengerjaan dipecah 2A (pengaturan, beranda,
  tentang, kontak, struktur organisasi), 2B (berita/pengumuman), 2C (galeri, prestasi).
- Pengaturan situs disimpan di `tb_pengaturan` (kunci-nilai; daftar kunci di `KatalogPengaturan`) dan dibaca lewat `situs('kunci')`
  dengan cache yang dibersihkan saat disimpan. Kunci yang belum tersimpan memakai nilai bawaan katalog, jadi situs tampil utuh tanpa seeder.
  Kunci yang sengaja dikosongkan tetap kosong. `config/sekolah.php` (`.env`) kini hanya sumber nilai bawaan identitas.
- **Konten bawaan (sejarah, visi-misi, hero, alamat, telepon, email, jam layanan) adalah teks contoh buatan, bukan data resmi yayasan.**
  Wajib diganti lewat Pengaturan Situs sebelum go-live. Peta (Google Maps embed) dan tautan media sosial kosong sampai diisi.
- Foto beranda/tentang memakai foto dummy template UniPulse yang disalin ke `public/img/situs/`; ganti dengan foto asli nanti.
  Logo dari `public/img/logo-sekolah.png` (1240x1240, 1,3 MB); header memakai salinan `logo-sekolah-256.png` agar ringan.
- Struktur organisasi disimpan di `tb_pengurus` (nama, jabatan, urutan, unit; unit kosong = tingkat yayasan; soft delete), terpisah dari
  `tb_pegawai` karena isinya bagan tampilan. Belum ada foto pengurus.
- Hak akses: `pengaturan.view/update` dan `pengurus.*` hanya `super_admin` (developer yang mengelola konten situs). Petugas TU hanya
  berita/pengumuman (§5). Ubah di `Role::permissions()` bila yayasan menginginkan lain.
- Halaman publik: `/` (beranda), `/tentang`, `/kontak`. Menu publik `Berita` dan `PPDB` muncul otomatis saat route-nya ada.

## Berita & pengumuman (Fase 2B)
- `tb_berita`: jenis (berita/pengumuman), judul, slug, ringkasan (opsional), isi, gambar (opsional), status (draf/terbit), tanggal terbit,
  penulis (`id_pegawai_penulis`, kosong bila dibuat akun tanpa pegawai), soft delete. Yang tampil di situs: status terbit dan tanggal terbit
  sudah tiba (tanggal mendatang = tayang terjadwal tanpa cron). Penulis tidak ditampilkan di situs publik.
- Isi berupa **teks biasa** (paragraf dipisah baris kosong), bukan editor WYSIWYG: tanpa package baru dan tanpa risiko XSS. Bila TU kelak
  butuh format (tebal, tautan), tambahkan editor lewat keputusan terpisah.
- Slug dibuat dari judul saat berita dibuat dan **tidak berubah** walau judul diubah (tautan lama tetap hidup); bentrok diberi akhiran -2, -3
  (termasuk dengan berita yang sudah dihapus).
- Gambar: JPG/PNG/WebP maksimal 4 MB dan 8000x8000 px; dikecilkan ke lebar maksimal 1200 px dan disimpan sebagai WebP (kualitas 80) di
  disk `public` folder `berita/`, memakai GD bawaan PHP lewat `PengolahGambar` (akan dipakai ulang galeri/prestasi). Gambar lama dibuang saat
  diganti/dihapus; berita yang di-soft delete menyimpan gambarnya. Lokal dan hosting perlu `php artisan storage:link` sekali.
- Beranda menampilkan 4 berita terbaru (di-cache 10 menit dan dibersihkan saat berita disimpan/dihapus). Daftar publik 9 per halaman,
  bisa difilter jenis. Draf, terjadwal, dan yang dihapus mengembalikan 404.
- Hak akses: `berita.*` untuk petugas TU dan super_admin (sesuai matriks yang sudah ada). Belum ada pembatasan per unit; berita bersifat tingkat yayasan.

## Galeri dan prestasi (Fase 2C)
- Disetujui 2026-10-01: galeri berupa kumpulan foto berjudul (tanpa album per kegiatan); prestasi berisi judul, nama peraih,
  tingkat (Sekolah, Kota/Kabupaten, Provinsi, Nasional), tahun, unit (kosong = yayasan), dan foto opsional.
- `tb_galeri`: judul, `gambar` (lebar maks. 1600 px untuk lightbox) dan `gambar_kecil` (480 px untuk grid), keduanya WebP. Satu foto per
  unggahan (belum ada unggah banyak sekaligus). `tb_prestasi`: foto opsional WebP lebar maks. 1200 px. Keduanya soft delete; berkas gambar
  lama dibuang saat diganti, dan disimpan bila datanya hanya di-soft delete.
- Urutan publik: galeri terbaru dulu (24 per halaman, lightbox GLightbox); prestasi tahun terbaru dulu, lalu tingkat tertinggi, 12 per halaman,
  bisa difilter tingkat. Beranda menampilkan 3 prestasi dan 6 foto terbaru.
- Hak akses: `galeri.*` dan `prestasi.*` hanya `super_admin`, sama seperti struktur organisasi (§5 hanya menyebut berita untuk TU).
  Bila TU diizinkan, cukup tambahkan `Izin::crud('galeri')`/`crud('prestasi')` di `Role::permissions()`.
- Cache beranda (`CacheBeranda`) menyimpan **atribut mentah (array)**, bukan objek model: Laravel tidak meng-unserialize kelas PHP dari cache
  (`serializable_classes` = false), sehingga model yang di-cache di driver `database`/`file` kembali sebagai `__PHP_Incomplete_Class`. Cache
  `array` pada test tidak men-serialize; `BerandaCacheTest` meniru cache nyata dengan `serialize => true`. Jangan menyimpan model/objek ke cache.

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
