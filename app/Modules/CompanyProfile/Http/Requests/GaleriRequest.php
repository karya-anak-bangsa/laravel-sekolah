<?php

namespace App\Modules\CompanyProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GaleriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            // wajib saat menambah; saat mengubah, kosong berarti foto lama dipertahankan
            'gambar' => [$this->isMethod('POST') ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
        ];
    }

    public function messages(): array
    {
        return [
            'gambar.required' => 'Pilih foto yang akan diunggah.',
            'gambar.max' => 'Ukuran foto maksimal 4 MB.',
            'gambar.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'gambar.dimensions' => 'Foto tidak valid atau terlalu besar (maksimal 8000 x 8000 piksel).',
            'gambar.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 4 MB.',
        ];
    }
}
