<?php

namespace App\Modules\CompanyProfile\Http\Requests;

use App\Modules\CompanyProfile\Enums\TingkatPrestasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrestasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return [
            'id_unit_sekolah' => ['nullable', 'exists:tb_unit_sekolah,id_unit_sekolah,deleted_at,NULL'],
            'judul' => ['required', 'string', 'max:200'],
            'nama_peraih' => ['required', 'string', 'max:150'],
            'tingkat' => ['required', Rule::enum(TingkatPrestasi::class)],
            'tahun' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'gambar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> data teks untuk disimpan (tanpa berkas gambar). */
    public function dataPrestasi(): array
    {
        return $this->safe()->except(['gambar', 'hapus_gambar']);
    }

    public function attributes(): array
    {
        return [
            'id_unit_sekolah' => 'unit',
            'nama_peraih' => 'nama peraih',
        ];
    }

    public function messages(): array
    {
        return [
            'gambar.max' => 'Ukuran foto maksimal 4 MB.',
            'gambar.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'gambar.dimensions' => 'Foto tidak valid atau terlalu besar (maksimal 8000 x 8000 piksel).',
            'gambar.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 4 MB.',
        ];
    }
}
