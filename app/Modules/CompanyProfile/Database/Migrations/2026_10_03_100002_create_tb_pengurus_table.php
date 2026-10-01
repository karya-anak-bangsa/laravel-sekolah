<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Struktur organisasi yang tampil di situs publik. Terpisah dari tb_pegawai karena isinya
        // bagan tampilan (mis. Ketua Pembina), bukan akun atau data kepegawaian.
        Schema::create('tb_pengurus', function (Blueprint $table) {
            $table->id('id_pengurus');
            $table->foreignId('id_unit_sekolah')->nullable()->constrained('tb_unit_sekolah', 'id_unit_sekolah'); // null = tingkat yayasan
            $table->string('nama_pengurus', 100);
            $table->string('jabatan', 100);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_unit_sekolah', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pengurus');
    }
};
