<?php

namespace App\Modules\Core\Http\Requests;

use App\Modules\Core\Enums\Semester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'nama_tahun_ajaran' => [
                'required', 'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tb_tahun_ajaran', 'nama_tahun_ajaran')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('tahun_ajaran')?->getKey(), 'id_tahun_ajaran'),
                function (string $attribute, mixed $value, \Closure $gagal) {
                    [$awal, $akhir] = array_map('intval', explode('/', (string) $value) + [1 => 0]);

                    if ($akhir !== $awal + 1) {
                        $gagal('Tahun ajaran harus berurutan, mis. 2026/2027.');
                    }
                },
            ],
            'semester_aktif' => ['required', Rule::enum(Semester::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
            'nama_tahun_ajaran.unique' => 'Tahun ajaran ini sudah terdaftar.',
        ];
    }
}
