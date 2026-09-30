<?php

namespace App\Modules\Kepegawaian\Http\Requests;

use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        $user = $this->user();
        $unitBoleh = UnitSekolah::query()->dapatDiaksesOleh($user)->pluck('id_unit_sekolah')->all();
        $pegawai = $this->route('pegawai');
        $yayasan = $this->input('jenis_pegawai') === JenisPegawai::PimpinanYayasan->value;

        return [
            'nama_pegawai' => ['required', 'string', 'max:100'],
            'jenis_pegawai' => [
                'required', Rule::enum(JenisPegawai::class),
                function (string $atribut, mixed $nilai, Closure $gagal) use ($user, $pegawai) {
                    // Hanya pengguna tingkat yayasan yang boleh mencatat pimpinan yayasan.
                    if ($nilai === JenisPegawai::PimpinanYayasan->value && $user->idUnitSekolah() !== null) {
                        $gagal('Anda tidak berwenang mencatat pimpinan yayasan.');
                    }

                    // Wali kelas harus tetap guru.
                    if ($pegawai && $nilai !== JenisPegawai::Guru->value && $pegawai->kelasDiampu()->exists()) {
                        $gagal('Pegawai ini masih menjadi wali kelas, jadi harus tetap berjenis guru.');
                    }
                },
            ],
            'id_unit_sekolah' => $yayasan ? ['prohibited'] : ['required', Rule::in($unitBoleh)],
        ];
    }

    public function messages(): array
    {
        return [
            'id_unit_sekolah.prohibited' => 'Pimpinan yayasan tidak terikat pada unit sekolah.',
            'id_unit_sekolah.in' => 'Unit sekolah yang dipilih tidak valid.',
            'id_unit_sekolah.required' => 'Unit sekolah wajib dipilih (kecuali pimpinan yayasan).',
        ];
    }
}
