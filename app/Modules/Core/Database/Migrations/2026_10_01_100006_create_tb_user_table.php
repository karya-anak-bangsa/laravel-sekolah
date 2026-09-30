<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->id('id_user');
            // terisi untuk akun pegawai, null untuk akun pendaftar PPDB
            $table->foreignId('id_pegawai')->nullable()->constrained('tb_pegawai', 'id_pegawai');
            $table->string('nama');
            $table->string('username')->nullable()->unique(); // login pegawai
            $table->string('no_hp', 20)->nullable()->unique(); // login pendaftar
            $table->string('email')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_user');
    }
};
