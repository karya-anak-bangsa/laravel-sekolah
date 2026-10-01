<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_prestasi', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->foreignId('id_unit_sekolah')->nullable()->constrained('tb_unit_sekolah', 'id_unit_sekolah'); // null = tingkat yayasan
            $table->string('judul', 200); // nama lomba/prestasi
            $table->string('nama_peraih', 150); // siswa, tim, atau sekolah
            $table->string('tingkat', 20); // sekolah | kota | provinsi | nasional
            $table->unsignedSmallInteger('tahun');
            $table->string('gambar')->nullable(); // path relatif di disk public
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun', 'tingkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_prestasi');
    }
};
