<?php

namespace App\Modules\CompanyProfile\Http\Requests;

use App\Modules\CompanyProfile\Enums\JenisBerita;
use App\Modules\CompanyProfile\Enums\StatusBerita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BeritaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::enum(JenisBerita::class)],
            'judul' => ['required', 'string', 'max:200'],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'isi' => ['required', 'string', 'max:20000'],
            'status' => ['required', Rule::enum(StatusBerita::class)],
            'tanggal_terbit' => ['nullable', 'date'],
            // dimensions juga memastikan berkasnya benar-benar gambar yang bisa dibaca
            'gambar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> data teks untuk disimpan (tanpa berkas gambar). */
    public function dataBerita(): array
    {
        return $this->safe()->except(['gambar', 'hapus_gambar']);
    }

    public function attributes(): array
    {
        return [
            'jenis' => 'jenis',
            'tanggal_terbit' => 'tanggal terbit',
            'isi' => 'isi',
        ];
    }

    public function messages(): array
    {
        return [
            'gambar.max' => 'Ukuran gambar maksimal 4 MB.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'gambar.dimensions' => 'Gambar tidak valid atau terlalu besar (maksimal 8000 x 8000 piksel).',
            'gambar.uploaded' => 'Gambar gagal diunggah. Pastikan ukurannya tidak lebih dari 4 MB.',
        ];
    }
}
