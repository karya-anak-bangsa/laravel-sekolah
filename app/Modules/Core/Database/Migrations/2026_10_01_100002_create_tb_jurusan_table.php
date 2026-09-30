<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_jurusan', function (Blueprint $table) {
            $table->id('id_jurusan');
            $table->foreignId('id_unit_sekolah')->constrained('tb_unit_sekolah', 'id_unit_sekolah');
            $table->string('nama_jurusan');
            $table->string('kode_jurusan', 20);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_jurusan');
    }
};
