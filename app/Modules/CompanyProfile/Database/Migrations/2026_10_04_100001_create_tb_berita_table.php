<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_berita', function (Blueprint $table) {
            $table->id('id_berita');
            $table->foreignId('id_pegawai_penulis')->nullable()->constrained('tb_pegawai', 'id_pegawai'); // null: dibuat akun tanpa pegawai (super_admin)
            $table->string('jenis', 20); // berita | pengumuman
            $table->string('judul', 200);
            $table->string('slug', 220)->unique();
            $table->string('ringkasan', 300)->nullable();
            $table->longText('isi');
            $table->string('gambar')->nullable(); // path relatif di disk public
            $table->string('status', 10)->default('draf'); // draf | terbit
            $table->dateTime('tanggal_terbit')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tanggal_terbit']);
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_berita');
    }
};
