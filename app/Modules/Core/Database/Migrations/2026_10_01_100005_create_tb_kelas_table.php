<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_kelas', function (Blueprint $table) {
            $table->id('id_kelas');
            $table->foreignId('id_unit_sekolah')->constrained('tb_unit_sekolah', 'id_unit_sekolah');
            // null untuk SMP (tanpa jurusan)
            $table->foreignId('id_jurusan')->nullable()->constrained('tb_jurusan', 'id_jurusan');
            $table->foreignId('id_tahun_ajaran')->constrained('tb_tahun_ajaran', 'id_tahun_ajaran');
            $table->unsignedTinyInteger('tingkat'); // 7-9 (SMP), 10-12 (SMK)
            $table->string('nama_kelas');
            $table->foreignId('id_pegawai_wali_kelas')->nullable()->constrained('tb_pegawai', 'id_pegawai');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_kelas');
    }
};
