<?php

namespace App\Modules\Core\Http\Requests;

use App\Modules\Core\Enums\Role;
use App\Modules\Kepegawaian\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->username)) {
            $this->merge(['username' => strtolower(trim($this->username))]);
        }
    }

    public function rules(): array
    {
        $pengguna = $this->route('pengguna');

        $rules = [
            'username' => [
                $pengguna ? 'required' : 'nullable',
                'string', 'regex:/^[a-z0-9._-]{3,50}$/',
                // Termasuk akun nonaktif: kolom username unik di seluruh baris.
                Rule::unique('tb_user', 'username')->ignore($pengguna?->getKey(), 'id_user'),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($this->roleYangBolehDipilih())],
        ];

        if (! $pengguna) {
            // Hanya pegawai yang terlihat oleh pengguna (scope unit) dan belum punya akun (aktif maupun nonaktif).
            $rules['id_pegawai'] = [
                'required',
                Rule::in(Pegawai::query()->whereDoesntHave('user', fn ($q) => $q->withTrashed())->pluck('id_pegawai')->all()),
            ];
        }

        return $rules;
    }

    /** Role untuk akun pegawai (pendaftar dikelola lewat PPDB). */
    private function roleYangBolehDipilih(): array
    {
        return collect(Role::cases())->reject(fn (Role $r) => $r === Role::Pendaftar)->map->value->all();
    }

    public function messages(): array
    {
        return [
            'id_pegawai.required' => 'Pegawai wajib dipilih.',
            'id_pegawai.in' => 'Pegawai yang dipilih tidak valid atau sudah memiliki akun.',
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, strip, dan garis bawah (3–50 karakter).',
            'username.unique' => 'Username ini sudah dipakai.',
            'username.required' => 'Username wajib diisi.',
            'roles.*.in' => 'Role yang dipilih tidak valid.',
        ];
    }
}
