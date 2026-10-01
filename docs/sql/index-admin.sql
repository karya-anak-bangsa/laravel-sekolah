-- ============================================================================
-- Query halaman index panel admin (http://localhost:8000/admin)
-- Database : db_sekolah (MySQL 8 / MariaDB 10.2+)
-- Dibuat   : 2026-10-01
--
-- Setiap bagian meniru query controller + view-nya (kolom, filter, urutan, ukuran halaman),
-- jadi hasil di DBeaver sama dengan tabel yang tampil di halaman.
--
-- Cara pakai di DBeaver
--   1. Buka file ini di SQL Editor (koneksi ke db_sekolah).
--   2. Blok-blok dipisah baris "-- ===". Di dalam satu blok TIDAK ada baris kosong.
--      Sorot satu blok lalu tekan Alt+X (Execute script): baris SET (filter) dan SELECT jalan
--      di satu sesi, hasilnya muncul di tab terpisah.
--   3. Ubah nilai @... pada baris SET untuk mencoba filter. NULL = tanpa filter
--      (sama seperti kolom filter dikosongkan di halaman).
--   4. Halaman 2 dst.: ubah OFFSET (= (halaman - 1) * ukuran halaman). Kolom total_baris =
--      jumlah seluruh baris sesudah filter (angka "Menampilkan x dari N" di paginasi).
--
-- Aturan yang ditiru dari aplikasi
--   * Soft delete: data master hanya yang deleted_at IS NULL (kecuali yang ditandai "withTrashed").
--   * Pengguna tingkat unit hanya melihat unitnya. Query di sini = sudut pandang super_admin /
--     pemilik_yayasan (semua unit). Untuk meniru kepala sekolah, tambahkan:
--         AND <alias>.id_unit_sekolah = <id unit>
--   * Nilai enum disimpan sebagai kode string (smp, guru, aktif, dst.); label di bawah = label di view.
-- ============================================================================


-- ============================================================================
-- 0. Dashboard  (/admin)  →  Core/resources/views/dashboard.blade.php
--    Halaman ini hanya kartu sambutan, tidak ada query data.
--    Query di bawah BUKAN bagian halaman; ringkasan isi database untuk orientasi di DBeaver.
-- ============================================================================
SELECT 'tb_unit_sekolah' AS tabel, COUNT(*) AS baris_aktif FROM tb_unit_sekolah WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_jurusan',      COUNT(*) FROM tb_jurusan      WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_tahun_ajaran', COUNT(*) FROM tb_tahun_ajaran WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_kelas',        COUNT(*) FROM tb_kelas        WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_pegawai',      COUNT(*) FROM tb_pegawai      WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_user',         COUNT(*) FROM tb_user         WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_siswa',        COUNT(*) FROM tb_siswa        WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_wali_siswa',   COUNT(*) FROM tb_wali_siswa   WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_anggota_kelas',COUNT(*) FROM tb_anggota_kelas WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_berita',       COUNT(*) FROM tb_berita       WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_galeri',       COUNT(*) FROM tb_galeri       WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_prestasi',     COUNT(*) FROM tb_prestasi     WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_pengurus',     COUNT(*) FROM tb_pengurus     WHERE deleted_at IS NULL
UNION ALL SELECT 'tb_pengaturan',   COUNT(*) FROM tb_pengaturan;


-- ============================================================================
-- MASTER DATA
-- ============================================================================

-- ============================================================================
-- 1. Pegawai  (/admin/pegawai)  →  Kepegawaian\PegawaiController@index   [25 per halaman]
--    Kolom view: Nama | Unit | Jenis | Akun
--    Tabel     : tb_pegawai ← tb_unit_sekolah (unit), tb_user (akun, withTrashed)
-- ============================================================================
SET @q = NULL;               -- cari nama pegawai, mis. 'budi'
SET @id_unit_sekolah = NULL; -- 1 = SMP, 2 = SMK (lihat bagian 5)
SET @jenis_pegawai = NULL;   -- 'guru' | 'tu' | 'pimpinan_yayasan'
SELECT
    p.id_pegawai,
    p.nama_pegawai                                   AS nama,
    COALESCE(UPPER(u.jenjang), 'Yayasan')            AS unit,
    CASE p.jenis_pegawai
        WHEN 'guru' THEN 'Guru'
        WHEN 'tu' THEN 'Tata Usaha'
        WHEN 'pimpinan_yayasan' THEN 'Pimpinan Yayasan'
    END                                              AS jenis,
    CASE
        WHEN us.id_user IS NULL THEN 'Belum ada akun'
        WHEN us.deleted_at IS NOT NULL THEN CONCAT(us.username, ' (Nonaktif)')
        ELSE us.username
    END                                              AS akun,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_pegawai p
LEFT JOIN tb_unit_sekolah u ON u.id_unit_sekolah = p.id_unit_sekolah AND u.deleted_at IS NULL
LEFT JOIN tb_user us        ON us.id_pegawai = p.id_pegawai   -- withTrashed: akun nonaktif tetap tampil
WHERE p.deleted_at IS NULL
  AND (@q IS NULL OR p.nama_pegawai LIKE CONCAT('%', @q, '%'))
  AND (@id_unit_sekolah IS NULL OR p.id_unit_sekolah = @id_unit_sekolah)
  AND (@jenis_pegawai IS NULL OR p.jenis_pegawai = @jenis_pegawai)
ORDER BY p.nama_pegawai
LIMIT 25 OFFSET 0;


-- ============================================================================
-- 2. Siswa  (/admin/siswa)  →  Kesiswaan\SiswaController@index   [25 per halaman]
--    Kolom view: Nama | NISN | L/P | Unit | Kelas | Status
--    Tabel     : tb_siswa ← tb_unit_sekolah; kelas = tb_anggota_kelas → tb_kelas → tb_tahun_ajaran (aktif)
--    Siswa tanpa kelas di tahun ajaran aktif tetap tampil (kelas '—'), jadi memakai LEFT JOIN.
--    Catatan: bila seorang siswa tercatat di >1 kelas pada tahun aktif, barisnya dobel di SQL,
--             sedangkan aplikasi hanya menampilkan yang pertama. Kondisi yang tidak seharusnya terjadi.
-- ============================================================================
SET @q = NULL;               -- cari nama ATAU NISN
SET @id_unit_sekolah = NULL;
SET @id_kelas = NULL;        -- kelas pada tahun ajaran aktif (lihat bagian 3)
SET @status_siswa = NULL;    -- 'calon' | 'aktif' | 'lulus' | 'pindah' | 'keluar'
SELECT
    s.id_siswa,
    s.nama_siswa                                     AS nama,
    COALESCE(s.nisn, '—')                            AS nisn,
    COALESCE(UPPER(s.jenis_kelamin), '—')            AS lp,
    UPPER(u.jenjang)                                 AS unit,
    COALESCE(k.nama_kelas, '—')                      AS kelas,
    s.status_siswa                                   AS status,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_siswa s
JOIN tb_unit_sekolah u         ON u.id_unit_sekolah = s.id_unit_sekolah
LEFT JOIN (tb_anggota_kelas ak
           JOIN tb_kelas k         ON k.id_kelas = ak.id_kelas AND k.deleted_at IS NULL
           JOIN tb_tahun_ajaran ta ON ta.id_tahun_ajaran = k.id_tahun_ajaran
                                  AND ta.is_aktif = 1 AND ta.deleted_at IS NULL)
       ON ak.id_siswa = s.id_siswa AND ak.deleted_at IS NULL
WHERE s.deleted_at IS NULL
  AND (@q IS NULL OR s.nama_siswa LIKE CONCAT('%', @q, '%') OR s.nisn LIKE CONCAT('%', @q, '%'))
  AND (@id_unit_sekolah IS NULL OR s.id_unit_sekolah = @id_unit_sekolah)
  AND (@id_kelas IS NULL OR k.id_kelas = @id_kelas)
  AND (@status_siswa IS NULL OR s.status_siswa = @status_siswa)
ORDER BY s.nama_siswa
LIMIT 25 OFFSET 0;


-- ============================================================================
-- 3. Kelas  (/admin/kelas)  →  Core\KelasController@index   [25 per halaman]
--    Kolom view: Kelas | Unit | Jurusan | Tingkat | Tahun Ajaran | Wali Kelas | Siswa
--    Tabel     : tb_kelas ← tb_unit_sekolah, tb_jurusan, tb_tahun_ajaran, tb_pegawai (wali), tb_anggota_kelas (jumlah)
--    Bawaan halaman: tahun ajaran aktif. Isi @id_tahun_ajaran = NULL untuk pilihan "Semua".
-- ============================================================================
SET @id_tahun_ajaran = (SELECT id_tahun_ajaran FROM tb_tahun_ajaran WHERE is_aktif = 1 AND deleted_at IS NULL LIMIT 1);
SET @id_unit_sekolah = NULL;
SET @tingkat = NULL;         -- 7..9 (SMP), 10..12 (SMK)
SELECT
    k.id_kelas,
    k.nama_kelas                                     AS kelas,
    UPPER(u.jenjang)                                 AS unit,
    COALESCE(j.nama_jurusan, '—')                    AS jurusan,
    k.tingkat,
    ta.nama_tahun_ajaran                             AS tahun_ajaran,
    COALESCE(w.nama_pegawai, '—')                    AS wali_kelas,
    (SELECT COUNT(*) FROM tb_anggota_kelas ak
      WHERE ak.id_kelas = k.id_kelas AND ak.deleted_at IS NULL) AS jumlah_siswa,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_kelas k
JOIN tb_unit_sekolah u       ON u.id_unit_sekolah = k.id_unit_sekolah
JOIN tb_tahun_ajaran ta      ON ta.id_tahun_ajaran = k.id_tahun_ajaran
LEFT JOIN tb_jurusan j       ON j.id_jurusan = k.id_jurusan
LEFT JOIN tb_pegawai w       ON w.id_pegawai = k.id_pegawai_wali_kelas AND w.deleted_at IS NULL
WHERE k.deleted_at IS NULL
  AND (@id_tahun_ajaran IS NULL OR k.id_tahun_ajaran = @id_tahun_ajaran)
  AND (@id_unit_sekolah IS NULL OR k.id_unit_sekolah = @id_unit_sekolah)
  AND (@tingkat IS NULL OR k.tingkat = @tingkat)
ORDER BY k.id_unit_sekolah, k.tingkat, k.nama_kelas
LIMIT 25 OFFSET 0;


-- ============================================================================
-- 4. Tahun Ajaran  (/admin/tahun-ajaran)  →  Core\TahunAjaranController@index   [semua baris]
--    Kolom view: Tahun Ajaran | Semester | Status
-- ============================================================================
SELECT
    ta.id_tahun_ajaran,
    ta.nama_tahun_ajaran                             AS tahun_ajaran,
    CASE ta.semester_aktif WHEN 'ganjil' THEN 'Ganjil' WHEN 'genap' THEN 'Genap' END AS semester,
    CASE WHEN ta.is_aktif = 1 THEN 'Aktif' ELSE 'Tidak aktif' END                      AS status
FROM tb_tahun_ajaran ta
WHERE ta.deleted_at IS NULL
ORDER BY ta.nama_tahun_ajaran DESC;


-- ============================================================================
-- 5. Unit & Jurusan  (/admin/unit-sekolah)  →  Core\UnitSekolahController@index   [semua baris]
--    Kolom view: Nama Unit | Jenjang | Jurusan (jumlah)
-- ============================================================================
SELECT
    u.id_unit_sekolah,
    u.nama_unit_sekolah                              AS nama_unit,
    UPPER(u.jenjang)                                 AS jenjang,
    (SELECT COUNT(*) FROM tb_jurusan j
      WHERE j.id_unit_sekolah = u.id_unit_sekolah AND j.deleted_at IS NULL) AS jumlah_jurusan
FROM tb_unit_sekolah u
WHERE u.deleted_at IS NULL
ORDER BY u.jenjang;

-- 5b. Daftar jurusan per unit (tampil di halaman "ubah unit", /admin/unit-sekolah/{id}/edit)
SELECT
    u.nama_unit_sekolah AS unit,
    j.id_jurusan,
    j.kode_jurusan,
    j.nama_jurusan
FROM tb_jurusan j
JOIN tb_unit_sekolah u ON u.id_unit_sekolah = j.id_unit_sekolah
WHERE j.deleted_at IS NULL AND u.deleted_at IS NULL
ORDER BY u.jenjang, j.nama_jurusan;


-- ============================================================================
-- SITUS (Company Profile)
-- ============================================================================

-- ============================================================================
-- 6. Pengaturan Situs  (/admin/pengaturan-situs)  →  CompanyProfile\PengaturanController@edit
--    Halaman berupa form, bukan tabel. Nilainya = tb_pengaturan (kunci, nilai); kunci yang belum
--    tersimpan memakai nilai bawaan dari kode (KatalogPengaturan), sehingga tabel boleh kosong.
--    Kelompok mengikuti KatalogPengaturan::KELOMPOK.
-- ============================================================================
SELECT
    CASE
        WHEN kunci IN ('nama', 'nama_singkat', 'deskripsi', 'hero_judul', 'hero_teks') THEN '1. Identitas & Beranda'
        WHEN kunci IN ('alamat', 'telepon', 'email', 'jam_layanan', 'peta_embed_url', 'instagram', 'facebook', 'youtube') THEN '2. Kontak & Lokasi'
        WHEN kunci IN ('sejarah', 'visi', 'misi') THEN '3. Profil Yayasan'
        ELSE '(kunci tidak dikenal)'
    END      AS kelompok,
    kunci,
    nilai,
    updated_at
FROM tb_pengaturan
ORDER BY kelompok, kunci;


-- ============================================================================
-- 7. Galeri Foto  (/admin/galeri)  →  CompanyProfile\GaleriController@index   [24 per halaman]
--    Kolom view: Foto (gambar_kecil) | Judul | Ditambahkan
-- ============================================================================
SELECT
    g.id_galeri,
    g.gambar_kecil                                   AS foto,
    g.judul,
    DATE_FORMAT(g.created_at, '%d/%m/%Y')            AS ditambahkan,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_galeri g
WHERE g.deleted_at IS NULL
ORDER BY g.created_at DESC, g.id_galeri DESC
LIMIT 24 OFFSET 0;


-- ============================================================================
-- 8. Prestasi  (/admin/prestasi)  →  CompanyProfile\PrestasiController@index   [25 per halaman]
--    Kolom view: Prestasi | Peraih | Tingkat | Tahun | Unit
-- ============================================================================
SET @q = NULL;               -- cari judul ATAU nama peraih
SET @tingkat = NULL;         -- 'sekolah' | 'kota' | 'provinsi' | 'nasional'
SET @tahun = NULL;           -- mis. 2026
SELECT
    p.id_prestasi,
    p.judul                                          AS prestasi,
    p.nama_peraih                                    AS peraih,
    CONCAT(UPPER(LEFT(p.tingkat, 1)), SUBSTRING(p.tingkat, 2)) AS tingkat,
    p.tahun,
    COALESCE(UPPER(u.jenjang), 'Yayasan')            AS unit,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_prestasi p
LEFT JOIN tb_unit_sekolah u ON u.id_unit_sekolah = p.id_unit_sekolah AND u.deleted_at IS NULL
WHERE p.deleted_at IS NULL
  AND (@q IS NULL OR p.judul LIKE CONCAT('%', @q, '%') OR p.nama_peraih LIKE CONCAT('%', @q, '%'))
  AND (@tingkat IS NULL OR p.tingkat = @tingkat)
  AND (@tahun IS NULL OR p.tahun = @tahun)
ORDER BY p.tahun DESC, FIELD(p.tingkat, 'nasional', 'provinsi', 'kota', 'sekolah'), p.id_prestasi DESC
LIMIT 25 OFFSET 0;


-- ============================================================================
-- 9. Struktur Organisasi  (/admin/pengurus)  →  CompanyProfile\PengurusController@index   [semua baris]
--    Kolom view: Nama | Jabatan | Tingkat | Urutan
--    Urutan: yayasan dahulu (id_unit_sekolah NULL), lalu per unit, lalu kolom urutan.
-- ============================================================================
SELECT
    pg.id_pengurus,
    pg.nama_pengurus                                 AS nama,
    pg.jabatan,
    COALESCE(u.nama_unit_sekolah, 'Yayasan')         AS tingkat,
    pg.urutan
FROM tb_pengurus pg
LEFT JOIN tb_unit_sekolah u ON u.id_unit_sekolah = pg.id_unit_sekolah AND u.deleted_at IS NULL
WHERE pg.deleted_at IS NULL
ORDER BY pg.id_unit_sekolah IS NOT NULL, pg.id_unit_sekolah, pg.urutan, pg.id_pengurus;


-- ============================================================================
-- 10. Berita & Pengumuman  (/admin/berita)  →  CompanyProfile\BeritaController@index   [25 per halaman]
--     Kolom view: Judul | Jenis | Status | Tanggal Terbit
--     Status di view: Tayang (terbit & tanggal <= sekarang) | Terjadwal (terbit & tanggal > sekarang) | Draf
--     Filter @status memakai kolom status mentah ('draf' | 'terbit'), sama seperti dropdown di halaman.
-- ============================================================================
SET @q = NULL;               -- cari judul
SET @jenis = NULL;           -- 'berita' | 'pengumuman'
SET @status = NULL;          -- 'draf' | 'terbit'
SELECT
    b.id_berita,
    b.judul,
    CONCAT(UPPER(LEFT(b.jenis, 1)), SUBSTRING(b.jenis, 2)) AS jenis,
    CASE
        WHEN b.status = 'terbit' AND b.tanggal_terbit <= NOW() THEN 'Tayang'
        WHEN b.status = 'terbit'                               THEN 'Terjadwal'
        ELSE 'Draf'
    END                                              AS status,
    COALESCE(DATE_FORMAT(b.tanggal_terbit, '%d/%m/%Y %H:%i'), '—') AS tanggal_terbit,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_berita b
WHERE b.deleted_at IS NULL
  AND (@q IS NULL OR b.judul LIKE CONCAT('%', @q, '%'))
  AND (@jenis IS NULL OR b.jenis = @jenis)
  AND (@status IS NULL OR b.status = @status)
ORDER BY b.tanggal_terbit IS NULL DESC, b.tanggal_terbit DESC, b.id_berita DESC   -- draf tanpa tanggal di atas
LIMIT 25 OFFSET 0;


-- ============================================================================
-- SISTEM
-- ============================================================================

-- ============================================================================
-- 11. Pengguna & Role  (/admin/pengguna)  →  Core\PenggunaController@index   [25 per halaman]
--     Kolom view: Nama | Username | Unit | Role | Status
--     withTrashed: akun nonaktif tetap tampil. Akun ber-role 'pendaftar' (calon siswa/wali) disembunyikan.
--     Role di spatie: model_has_roles.model_id = tb_user.id_user (model_type berisi nama class User).
-- ============================================================================
SET @q = NULL;               -- cari nama ATAU username
SET @role = NULL;            -- mis. 'petugas_tu', 'kepala_sekolah', 'super_admin'
SELECT
    us.id_user,
    us.nama,
    us.username,
    CASE WHEN us.id_pegawai IS NULL THEN '—' ELSE COALESCE(UPPER(u.jenjang), 'Yayasan') END AS unit,
    COALESCE((SELECT GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ')
                FROM model_has_roles mr
                JOIN roles r ON r.id = mr.role_id
               WHERE mr.model_id = us.id_user AND mr.model_type LIKE '%User'), 'Belum ada role') AS role,
    CASE
        WHEN us.deleted_at IS NOT NULL THEN 'Nonaktif'
        WHEN us.wajib_ganti_password = 1 THEN 'Menunggu ganti sandi'
        ELSE 'Aktif'
    END                                              AS status,
    COUNT(*) OVER ()                                 AS total_baris
FROM tb_user us
LEFT JOIN tb_pegawai p      ON p.id_pegawai = us.id_pegawai AND p.deleted_at IS NULL
LEFT JOIN tb_unit_sekolah u ON u.id_unit_sekolah = p.id_unit_sekolah AND u.deleted_at IS NULL
WHERE NOT EXISTS (SELECT 1 FROM model_has_roles mr
                    JOIN roles r ON r.id = mr.role_id
                   WHERE mr.model_id = us.id_user AND mr.model_type LIKE '%User' AND r.name = 'pendaftar')
  AND (@q IS NULL OR us.nama LIKE CONCAT('%', @q, '%') OR us.username LIKE CONCAT('%', @q, '%'))
  AND (@role IS NULL OR EXISTS (SELECT 1 FROM model_has_roles mr
                                  JOIN roles r ON r.id = mr.role_id
                                 WHERE mr.model_id = us.id_user AND mr.model_type LIKE '%User' AND r.name = @role))
ORDER BY us.nama
LIMIT 25 OFFSET 0;


-- ============================================================================
-- 12. PPDB  (menu "Pendaftaran", route admin.ppdb.index)
--     Belum ada: modul Ppdb belum punya migration, controller, maupun route admin, sehingga menu
--     otomatis tersembunyi (config/menu.php menyaring route yang belum terdaftar).
--     Tabel rencananya (tb_gelombang, tb_pendaftaran) ada di CLAUDE.md §4 dan docs/ppdb-formulir.md.
--     Tambahkan query halaman ini setelah migration PPDB dibuat.
-- ============================================================================
