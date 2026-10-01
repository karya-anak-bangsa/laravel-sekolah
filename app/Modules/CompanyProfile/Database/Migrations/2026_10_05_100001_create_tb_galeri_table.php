<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_galeri', function (Blueprint $table) {
            $table->id('id_galeri');
            $table->string('judul', 150);
            $table->string('gambar'); // path relatif di disk public (lebar maks. 1600 px)
            $table->string('gambar_kecil'); // thumbnail untuk grid (lebar 480 px)
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_galeri');
    }
};
