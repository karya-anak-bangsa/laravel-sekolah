<?php

namespace App\Modules\CompanyProfile\Http\Requests;

use App\Modules\CompanyProfile\Support\KatalogPengaturan;
use Illuminate\Foundation\Http\FormRequest;

class PengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        return collect(KatalogPengaturan::semua())
            ->mapWithKeys(fn (array $definisi, string $kunci) => [$kunci => ['nullable', 'string', ...$definisi['aturan']]])
            ->map(fn (array $aturan) => array_values(array_unique($aturan)))
            ->all();
    }

    public function attributes(): array
    {
        return collect(KatalogPengaturan::semua())->map(fn (array $definisi) => mb_strtolower($definisi['label']))->all();
    }

    public function messages(): array
    {
        return [
            'peta_embed_url.starts_with' => 'Alamat peta harus berupa alamat sematan Google Maps (diawali https://www.google.com/maps/embed).',
            '*.url' => ':Attribute harus berupa alamat https yang valid.',
        ];
    }
}
