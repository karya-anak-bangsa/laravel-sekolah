<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_wali_siswa', function (Blueprint $table) {
            $table->id('id_wali_siswa');
            $table->string('nama_wali_siswa')->index();
            $table->string('pendidikan', 20)->nullable();
            $table->string('pekerjaan', 30)->nullable();
            $table->string('penghasilan', 20)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('agama', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Satu orang tua/wali dapat dimiliki beberapa siswa (kakak-adik); satu siswa punya paling banyak
        // satu ayah, satu ibu, dan satu wali.
        Schema::create('tb_siswa_wali', function (Blueprint $table) {
            $table->foreignId('id_siswa')->constrained('tb_siswa', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('id_wali_siswa')->constrained('tb_wali_siswa', 'id_wali_siswa');
            $table->string('hubungan', 10); // ayah, ibu, wali
            $table->string('hubungan_keluarga', 50)->nullable(); // khusus wali, mis. paman
            $table->timestamps();

            $table->primary(['id_siswa', 'hubungan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_siswa_wali');
        Schema::dropIfExists('tb_wali_siswa');
    }
};
