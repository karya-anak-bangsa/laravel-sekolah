# Modul PPDB — Alur, Formulir & Pemetaan ke Tabel

Acuan: **formulir pendaftaran** (Lembar Peserta Didik) dan **surat pernyataan** berbentuk kertas dari sekolah.
Formulir online mereplikasi kedua lembar tersebut. Tidak ada berkas yang wajib diunggah.
Tidak ada seleksi nilai — semua pendaftar diasumsikan diterima. Data pengembangan adalah **dummy**.

## Prinsip
- **Satu jalur, satu logika.** Semua pendaftaran melalui situs publik dengan akun pendaftar.
  Pendaftar tanpa laptop/PC datang ke sekolah; petugas TU membantu membuat akun dan mengisi formulir
  **memakai halaman yang sama** di komputer sekolah. Tidak ada form input PPDB terpisah di panel admin.
- PPDB tidak punya tabel identitas sendiri: identitas masuk ke master `tb_siswa`, orang tua/wali ke
  `tb_wali_siswa` (+ pivot `tb_siswa_wali`). Tabel PPDB hanya mencatat proses pendaftaran.
- Tidak ada notifikasi WA/email. Status dipantau pendaftar dari dasbor akunnya.

## Alur
1. **Registrasi akun** (role `pendaftar`): nama, no HP (dipakai untuk login), email opsional, password.
2. **Pilih unit & gelombang aktif.** Bila unit SMK, **wajib memilih jurusan**.
3. **Isi formulir bertahap** (ramah HP, bisa disimpan sebagai draf dan dilanjutkan):
   identitas siswa → data ayah → data ibu → data wali (opsional) → data tambahan surat pernyataan →
   persetujuan pernyataan → ringkasan.
4. **Kirim** → status `menunggu_verifikasi`; nomor pendaftaran terbit. Pendaftar dapat mencetak
   formulir pendaftaran dan surat pernyataan (PDF, format menyerupai kertas asli) sebagai bukti.
5. **Pembayaran** biaya pendaftaran dilakukan di sekolah; petugas TU mencatatnya di panel admin.
6. **Verifikasi** oleh petugas TU. **Hanya bisa dilakukan bila `status_pembayaran = lunas`**; aturan ini
   ditegakkan di Action/Policy (bukan hanya disembunyikan di UI) dan diuji dengan feature test.
   Bila ada data salah → `perlu_perbaikan` + catatan; pendaftar memperbaiki lalu mengirim ulang. Pendaftaran palsu → `ditolak` (data siswa di-soft delete).
7. **Penerimaan:** petugas TU menetapkan kelas → `diterima`; `tb_siswa.status_siswa` menjadi `aktif`,
   dibuat baris `tb_anggota_kelas`.

Status: `draf`, `menunggu_verifikasi`, `perlu_perbaikan`, `terverifikasi`, `diterima`, `ditolak`, `mengundurkan_diri`.
Pendaftar hanya dapat mengubah data saat status `draf` atau `perlu_perbaikan`.

## Gelombang & biaya
- Tiga gelombang per tahun ajaran, **berlaku sama untuk SMP dan SMK** (tidak ada `id_unit_sekolah` di `tb_gelombang`), dikelola petugas TU di panel admin.
- Biaya pendaftaran **flat Rp200.000**, disimpan di `tb_gelombang.biaya_pendaftaran` (bukan hardcode),
  lalu **disalin** ke `tb_pendaftaran.biaya_pendaftaran` saat mendaftar agar histori tidak berubah bila tarif diganti.
- Hanya gelombang yang tanggalnya sedang berjalan yang bisa dipilih.

### `tb_gelombang`
id_gelombang, id_tahun_ajaran, nama_gelombang (Gelombang 1/2/3), tanggal_mulai, tanggal_selesai,
biaya_pendaftaran (integer, default 200000), timestamps.

## `tb_pendaftaran`
| Kolom | Catatan |
|-------|---------|
| id_pendaftaran | PK |
| no_pendaftaran | unik, terbit saat dikirim, mis. `PPDB-SMK-2627-G1-0001` (dibuat dengan penguncian) |
| id_user | akun pendaftar pemilik data |
| id_siswa | calon siswa (`status_siswa = calon`) |
| id_unit_sekolah, id_tahun_ajaran, id_gelombang | |
| id_jurusan | nullable; **wajib bila unit SMK** (`required_if` + cek jurusan milik unit SMK) |
| status, catatan_verifikasi | |
| tanggal_kirim | |
| setuju_pernyataan_at | waktu pendaftar mencentang persetujuan pernyataan |
| teks_pernyataan | salinan teks butir pernyataan yang disetujui (arsip versi) |
| biaya_pendaftaran | salinan dari gelombang |
| status_pembayaran | `belum_bayar` / `lunas` |
| tanggal_bayar, id_pegawai_penerima_pembayaran | nullable, diisi petugas TU |
| id_pegawai_verifikator, tanggal_verifikasi | nullable |
| id_kelas_diterima | nullable |

## Lembar 1 — Peserta Didik → `tb_siswa`
| Kolom | Tipe | Nilai / catatan |
|-------|------|-----------------|
| nama_siswa | string | |
| jenis_kelamin | enum `JenisKelamin` | l, p |
| nisn | char(10) nullable, unik | 10 digit |
| no_seri_ijazah, no_seri_skhus | string nullable | |
| tempat_lahir, tanggal_lahir | string, date | |
| agama | enum `Agama` | islam, katolik, kristen, hindu, budha, konghucu |
| kebutuhan_khusus | enum `KebutuhanKhusus` | tidak_ada, tuna_netra, tuna_rungu, grahita_ringan, grahita_sedang, indigo, down_sindrom, autis, tuna_wicara, tuna_ganda, hiper_aktif, kesulitan_belajar, narkoba |
| alamat_jalan, desa_kelurahan, kecamatan, kabupaten_kota | string | |
| kode_pos | char(5) nullable | |
| moda_transportasi | enum `ModaTransportasi` | jalan_kaki, kendaraan_pribadi, kendaraan_umum, jemputan_sekolah |
| tempat_tinggal | enum `TempatTinggal` | bersama_orang_tua, bersama_wali, kos, asrama, panti_asuhan, lainnya |
| no_hp | string nullable | nomor WA, dinormalisasi ke format 08xx |
| email | string nullable | |
| no_kps_pkh, no_kip | string nullable | |

## Lembar 1 — Ayah, Ibu, Wali → `tb_wali_siswa` + `tb_siswa_wali`
Satu baris `tb_wali_siswa` per orang; hubungan disimpan di pivot.

| Kolom | Tipe | Nilai / catatan |
|-------|------|-----------------|
| nama_wali_siswa | string | |
| pendidikan | enum `Pendidikan` | tidak_sekolah, sd, smp, sma, d1, d2, d3, s1, s2, s3 |
| pekerjaan | enum `Pekerjaan` | tidak_bekerja, nelayan, petani, tni_polri, karyawan_swasta, pns, pedagang_kecil, pedagang_besar, wiraswasta, buruh, pensiunan, lainnya |
| penghasilan | enum `Penghasilan` | kurang_500rb, 500rb_1jt, 1jt_2jt, 2jt_5jt, 5jt_20jt, lebih_20jt |
| no_hp | string nullable | |
| agama | enum `Agama` nullable | dari surat pernyataan |
| alamat | text nullable | dari surat pernyataan; default sama dengan alamat siswa |

`tb_siswa_wali`: id_siswa, id_wali_siswa, hubungan (`ayah`/`ibu`/`wali`), hubungan_keluarga (nullable, khusus wali).
Ayah dan ibu wajib; wali opsional.

## Lembar 2 — Surat Pernyataan
- Field identitas (nama, TTL, jenis kelamin, agama, nama & pekerjaan orang tua/wali) **diambil dari Lembar 1**,
  tidak diinput ulang.
- Field yang hanya ada di lembar ini: agama orang tua, hubungan keluarga dengan wali, alamat orang tua/wali
  (diisi pendaftar), dan "diterima di kelas" (diisi petugas TU saat penerimaan).
- Butir-butir pernyataan (kesungguhan belajar, mematuhi peraturan sekolah, dll.) ditampilkan di formulir
  online dan wajib disetujui lewat checkbox. Teks butir disimpan di `tb_pengaturan` agar bisa diubah tanpa kode;
  salin teksnya ke pendaftaran (`teks_pernyataan`) saat disetujui sebagai arsip versi.
- Tanda tangan basah tetap di kertas: surat pernyataan dicetak dari sistem saat daftar ulang.

## Fitur panel admin (petugas TU)
Kelola gelombang, daftar & filter pendaftar (unit, jurusan, gelombang, status, pembayaran), verifikasi,
catat pembayaran, penempatan kelas, cetak formulir & surat pernyataan, reset password akun pendaftar,
rekap jumlah pendaftar per unit/jurusan/gelombang.
