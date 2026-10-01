<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pasangan kunci-nilai untuk konten situs (identitas, kontak, sejarah, visi-misi).
        // Daftar kunci yang dikenal ada di App\Modules\CompanyProfile\Support\KatalogPengaturan.
        Schema::create('tb_pengaturan', function (Blueprint $table) {
            $table->id('id_pengaturan');
            $table->string('kunci', 50)->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pengaturan');
    }
};
