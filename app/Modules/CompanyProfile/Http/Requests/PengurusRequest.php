<?php

namespace App\Modules\CompanyProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengurusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'id_unit_sekolah' => ['nullable', 'exists:tb_unit_sekolah,id_unit_sekolah,deleted_at,NULL'],
            'nama_pengurus' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    /** Data tervalidasi untuk disimpan; urutan kosong dianggap 0. */
    public function untukDisimpan(): array
    {
        $data = $this->validated();
        $data['urutan'] = (int) ($data['urutan'] ?? 0);

        return $data;
    }

    public function attributes(): array
    {
        return [
            'id_unit_sekolah' => 'unit',
            'nama_pengurus' => 'nama',
        ];
    }
}
