<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_unit_sekolah', function (Blueprint $table) {
            $table->id('id_unit_sekolah');
            $table->string('nama_unit_sekolah');
            $table->string('jenjang', 10)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_unit_sekolah');
    }
};
