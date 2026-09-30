<?php

namespace App\Modules\Kesiswaan\Http\Requests;

use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\HubunganWali;
use App\Modules\Kesiswaan\Enums\JenisKelamin;
use App\Modules\Kesiswaan\Enums\KebutuhanKhusus;
use App\Modules\Kesiswaan\Enums\ModaTransportasi;
use App\Modules\Kesiswaan\Enums\Pekerjaan;
use App\Modules\Kesiswaan\Enums\Pendidikan;
use App\Modules\Kesiswaan\Enums\Penghasilan;
use App\Modules\Kesiswaan\Enums\StatusSiswa;
use App\Modules\Kesiswaan\Enums\TempatTinggal;
use App\Support\NomorHp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if (filled($this->no_hp)) {
            $data['no_hp'] = NomorHp::normalisasi($this->no_hp);
        }

        $wali = $this->input('wali');

        if (is_array($wali)) {
            foreach ($wali as $hubungan => $isian) {
                if (is_array($isian) && filled($isian['no_hp'] ?? null)) {
                    $wali[$hubungan]['no_hp'] = NomorHp::normalisasi($isian['no_hp']);
                }
            }
            $data['wali'] = $wali;
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $siswa = $this->route('siswa');
        $noHp = ['nullable', 'regex:/^08\d{8,12}$/'];

        $rules = [
            'status_siswa' => ['required', Rule::enum(StatusSiswa::class)],
            'nama_siswa' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'nisn' => [
                'nullable', 'digits:10',
                Rule::unique('tb_siswa', 'nisn')->whereNull('deleted_at')->ignore($siswa?->getKey(), 'id_siswa'),
            ],
            'no_seri_ijazah' => ['nullable', 'string', 'max:50'],
            'no_seri_skhus' => ['nullable', 'string', 'max:50'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today', 'after:1990-01-01'],
            'agama' => ['required', Rule::enum(Agama::class)],
            'kebutuhan_khusus' => ['required', Rule::enum(KebutuhanKhusus::class)],
            'alamat_jalan' => ['nullable', 'string', 'max:255'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'digits:5'],
            'moda_transportasi' => ['nullable', Rule::enum(ModaTransportasi::class)],
            'tempat_tinggal' => ['nullable', Rule::enum(TempatTinggal::class)],
            'no_hp' => $noHp,
            'email' => ['nullable', 'email', 'max:255'],
            'no_kps_pkh' => ['nullable', 'string', 'max:50'],
            'no_kip' => ['nullable', 'string', 'max:50'],
            'wali' => ['required', 'array'],
        ];

        foreach (HubunganWali::cases() as $hubungan) {
            $p = "wali.{$hubungan->value}";
            $wajib = $hubungan !== HubunganWali::Wali; // ayah dan ibu wajib, wali opsional

            $rules["{$p}.nama_wali_siswa"] = [$wajib ? 'required' : 'nullable', 'string', 'max:150'];
            $rules["{$p}.pendidikan"] = ['nullable', Rule::enum(Pendidikan::class)];
            $rules["{$p}.pekerjaan"] = ['nullable', Rule::enum(Pekerjaan::class)];
            $rules["{$p}.penghasilan"] = ['nullable', Rule::enum(Penghasilan::class)];
            $rules["{$p}.no_hp"] = $noHp;
            $rules["{$p}.agama"] = ['nullable', Rule::enum(Agama::class)];
            $rules["{$p}.alamat"] = ['nullable', 'string', 'max:500'];
        }

        $rules['wali.wali.hubungan_keluarga'] = ['nullable', 'string', 'max:50'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nisn.digits' => 'NISN harus terdiri dari 10 digit angka.',
            'nisn.unique' => 'NISN ini sudah terdaftar.',
            'no_hp.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_lahir.after' => 'Tanggal lahir tidak valid.',
            'kode_pos.digits' => 'Kode pos harus terdiri dari 5 digit angka.',
            'wali.*.no_hp.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
        ];
    }

    public function attributes(): array
    {
        $atribut = [];

        foreach (HubunganWali::cases() as $hubungan) {
            $nama = $hubungan->label();
            $p = "wali.{$hubungan->value}";
            $atribut["{$p}.nama_wali_siswa"] = "Nama {$nama}";
            $atribut["{$p}.pendidikan"] = "Pendidikan {$nama}";
            $atribut["{$p}.pekerjaan"] = "Pekerjaan {$nama}";
            $atribut["{$p}.penghasilan"] = "Penghasilan {$nama}";
            $atribut["{$p}.no_hp"] = "Nomor HP {$nama}";
            $atribut["{$p}.agama"] = "Agama {$nama}";
            $atribut["{$p}.alamat"] = "Alamat {$nama}";
        }

        return $atribut + [
            'wali.wali.hubungan_keluarga' => 'Hubungan keluarga wali',
            'nisn' => 'NISN',
            'no_kps_pkh' => 'No. KPS/PKH',
            'no_kip' => 'No. KIP',
            'no_seri_ijazah' => 'No. seri ijazah',
            'no_seri_skhus' => 'No. seri SKHUS',
        ];
    }
}
