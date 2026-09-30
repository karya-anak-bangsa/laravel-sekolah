<?php

namespace App\Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->kode_jurusan)) {
            $this->merge(['kode_jurusan' => strtoupper(trim($this->kode_jurusan))]);
        }
    }

    public function rules(): array
    {
        // Unit dari route (create: {unit_sekolah}) atau dari jurusan yang diedit ({jurusan}).
        $idUnit = $this->route('unit_sekolah')?->getKey() ?? $this->route('jurusan')?->id_unit_sekolah;

        return [
            'nama_jurusan' => ['required', 'string', 'max:100'],
            'kode_jurusan' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('tb_jurusan', 'kode_jurusan')
                    ->where('id_unit_sekolah', $idUnit)
                    ->whereNull('deleted_at')
                    ->ignore($this->route('jurusan')?->getKey(), 'id_jurusan'),
            ],
        ];
    }
}
