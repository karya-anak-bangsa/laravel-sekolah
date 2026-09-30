<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_anggota_kelas', function (Blueprint $table) {
            $table->id('id_anggota_kelas');
            $table->foreignId('id_siswa')->constrained('tb_siswa', 'id_siswa');
            $table->foreignId('id_kelas')->constrained('tb_kelas', 'id_kelas');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_kelas', 'id_siswa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_anggota_kelas');
    }
};
