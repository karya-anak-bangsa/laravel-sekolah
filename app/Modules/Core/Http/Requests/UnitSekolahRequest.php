<?php

namespace App\Modules\Core\Http\Requests;

use App\Modules\Core\Enums\Jenjang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'nama_unit_sekolah' => ['required', 'string', 'max:100'],
            'jenjang' => ['required', Rule::enum(Jenjang::class)],
        ];
    }
}
