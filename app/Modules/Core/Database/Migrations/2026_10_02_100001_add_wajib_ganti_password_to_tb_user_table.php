<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_user', function (Blueprint $table) {
            // true untuk akun baru / hasil reset: pengguna wajib mengganti kata sandi saat login berikutnya
            $table->boolean('wajib_ganti_password')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('tb_user', function (Blueprint $table) {
            $table->dropColumn('wajib_ganti_password');
        });
    }
};
