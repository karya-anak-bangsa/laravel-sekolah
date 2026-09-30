<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_siswa', function (Blueprint $table) {
            $table->id('id_siswa');
            $table->foreignId('id_unit_sekolah')->constrained('tb_unit_sekolah', 'id_unit_sekolah');
            $table->string('status_siswa', 20)->default('calon')->index(); // calon, aktif, lulus, pindah, keluar

            // Identitas (Lembar 1 formulir PPDB). Dibuat nullable karena pendaftar boleh menyimpan draf;
            // kelengkapan divalidasi di aplikasi, bukan di database.
            $table->string('nama_siswa')->index();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->char('nisn', 10)->nullable();
            // NISN harus unik di antara siswa yang belum dihapus; siswa yang di-soft delete (mis. pendaftaran palsu)
            // tidak boleh menghalangi pendaftar asli memakai NISN yang sama.
            $table->char('nisn_aktif', 10)->nullable()->virtualAs('IF(deleted_at IS NULL, nisn, NULL)')->unique();
            $table->string('no_seri_ijazah', 50)->nullable();
            $table->string('no_seri_skhus', 50)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('kebutuhan_khusus', 30)->default('tidak_ada');
            $table->string('alamat_jalan')->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->char('kode_pos', 5)->nullable();
            $table->string('moda_transportasi', 30)->nullable();
            $table->string('tempat_tinggal', 30)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('no_kps_pkh', 50)->nullable();
            $table->string('no_kip', 50)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_siswa');
    }
};
