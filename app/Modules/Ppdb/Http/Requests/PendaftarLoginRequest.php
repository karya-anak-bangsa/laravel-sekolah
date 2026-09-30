<?php

namespace App\Modules\Ppdb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PendaftarLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_hp' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }
}
