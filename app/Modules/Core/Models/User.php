<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Database\Factories\UserFactory;
use App\Modules\Core\Models\Scopes\UnitSekolahScope;
use App\Modules\Core\Policies\PenggunaPolicy;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Table('tb_user', key: 'id_user')]
#[Fillable(['id_pegawai', 'nama', 'username', 'no_hp', 'email', 'password', 'wajib_ganti_password'])]
#[UsePolicy(PenggunaPolicy::class)]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    private ?int $idUnitSekolah = null;

    private bool $idUnitSekolahTerisi = false;

    /** Apakah pengguna boleh mengakses data milik unit tertentu (tingkat yayasan/super_admin: semua unit). */
    public function dapatMengaksesUnit(?int $idUnitSekolah): bool
    {
        $milikku = $this->idUnitSekolah();

        return $milikku === null || $milikku === $idUnitSekolah;
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }

    /**
     * Unit tempat pengguna bertugas, atau null bila tingkat yayasan / bukan pegawai (mis. super_admin).
     * Dibaca tanpa global scope agar UnitSekolahScope tidak memanggil dirinya sendiri.
     */
    public function idUnitSekolah(): ?int
    {
        if (! $this->idUnitSekolahTerisi) {
            $this->idUnitSekolah = $this->id_pegawai === null
                ? null
                : Pegawai::withoutGlobalScope(UnitSekolahScope::class)
                    ->whereKey($this->id_pegawai)
                    ->value('id_unit_sekolah');
            $this->idUnitSekolahTerisi = true;
        }

        return $this->idUnitSekolah;
    }
}
