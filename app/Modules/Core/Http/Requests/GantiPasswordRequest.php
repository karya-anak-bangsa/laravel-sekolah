<?php

namespace App\Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class GantiPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // setiap pengguna login boleh mengganti kata sandinya sendiri
    }

    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:password_lama', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'password_lama.required' => 'Kata sandi lama wajib diisi.',
            'password_lama.current_password' => 'Kata sandi lama tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi lama.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ];
    }
}
