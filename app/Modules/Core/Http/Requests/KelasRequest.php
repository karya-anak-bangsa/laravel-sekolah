<?php

namespace App\Modules\Core\Http\Requests;

use App\Modules\Core\Enums\Jenjang;
use App\Modules\Core\Models\UnitSekolah;
use App\Modules\Kepegawaian\Enums\JenisPegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi lewat Policy di controller
    }

    public function rules(): array
    {
        $unitBoleh = UnitSekolah::query()->dapatDiaksesOleh($this->user())->pluck('jenjang', 'id_unit_sekolah');
        $idUnit = (int) $this->input('id_unit_sekolah');
        $jenjang = isset($unitBoleh[$idUnit]) ? $unitBoleh[$idUnit] : null;
        $jenjang = $jenjang instanceof Jenjang ? $jenjang : Jenjang::tryFrom((string) $jenjang);

        $tingkat = match ($jenjang) {
            Jenjang::Smp => range(7, 9),
            Jenjang::Smk => range(10, 12),
            default => range(7, 12),
        };

        $jurusan = match ($jenjang) {
            Jenjang::Smk => [
                'required',
                Rule::exists('tb_jurusan', 'id_jurusan')->where('id_unit_sekolah', $idUnit)->whereNull('deleted_at'),
            ],
            Jenjang::Smp => ['prohibited'],
            default => ['nullable'],
        };

        return [
            'id_unit_sekolah' => ['required', Rule::in($unitBoleh->keys()->all())],
            'id_tahun_ajaran' => ['required', Rule::exists('tb_tahun_ajaran', 'id_tahun_ajaran')->whereNull('deleted_at')],
            'tingkat' => ['required', 'integer', Rule::in($tingkat)],
            'id_jurusan' => $jurusan,
            'nama_kelas' => [
                'required', 'string', 'max:50',
                Rule::unique('tb_kelas', 'nama_kelas')
                    ->where('id_unit_sekolah', $idUnit)
                    ->where('id_tahun_ajaran', $this->input('id_tahun_ajaran'))
                    ->whereNull('deleted_at')
                    ->ignore($this->route('kelas')?->getKey(), 'id_kelas'),
            ],
            'id_pegawai_wali_kelas' => [
                'nullable',
                Rule::exists('tb_pegawai', 'id_pegawai')
                    ->where('id_unit_sekolah', $idUnit)
                    ->where('jenis_pegawai', JenisPegawai::Guru->value)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_unit_sekolah.in' => 'Unit sekolah yang dipilih tidak valid.',
            'tingkat.in' => 'Tingkat tidak sesuai dengan jenjang unit (SMP: 7–9, SMK: 10–12).',
            'id_jurusan.required' => 'Jurusan wajib dipilih untuk kelas SMK.',
            'id_jurusan.prohibited' => 'Kelas SMP tidak memiliki jurusan.',
            'id_jurusan.exists' => 'Jurusan yang dipilih bukan milik unit ini.',
            'nama_kelas.unique' => 'Nama kelas ini sudah dipakai pada unit dan tahun ajaran yang sama.',
            'id_pegawai_wali_kelas.exists' => 'Wali kelas harus guru pada unit yang sama.',
        ];
    }
}
