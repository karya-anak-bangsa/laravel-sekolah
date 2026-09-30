<?php

namespace App\Modules\Kesiswaan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenempatanKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            // Hanya kelas pada unit siswa; scope unit pengguna diterapkan oleh model Kelas.
            'id_kelas' => ['required', Rule::exists('tb_kelas', 'id_kelas')
                ->where('id_unit_sekolah', $this->route('siswa')?->id_unit_sekolah)
                ->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'id_kelas.required' => 'Kelas wajib dipilih.',
            'id_kelas.exists' => 'Kelas yang dipilih tidak valid untuk unit siswa ini.',
        ];
    }

    public function attributes(): array
    {
        return ['id_kelas' => 'Kelas'];
    }
}
