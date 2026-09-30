<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_pegawai', function (Blueprint $table) {
            $table->id('id_pegawai');
            // null = pegawai tingkat yayasan
            $table->foreignId('id_unit_sekolah')->nullable()->constrained('tb_unit_sekolah', 'id_unit_sekolah');
            $table->string('nama_pegawai');
            $table->string('jenis_pegawai', 20)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pegawai');
    }
};
