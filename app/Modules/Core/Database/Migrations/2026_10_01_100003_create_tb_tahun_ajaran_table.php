<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_tahun_ajaran', function (Blueprint $table) {
            $table->id('id_tahun_ajaran');
            $table->string('nama_tahun_ajaran', 9); // contoh: 2026/2027
            $table->string('semester_aktif', 10);
            $table->boolean('is_aktif')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_tahun_ajaran');
    }
};
