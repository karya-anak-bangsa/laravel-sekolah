@extends('layouts.admin')

@section('title', 'Ubah Data Siswa')
@section('pretitle', $siswa->nama_siswa)

@section('content')
    <form method="POST" action="{{ route('admin.siswa.update', $siswa) }}">
        @csrf
        @method('PUT')

        <x-admin.card title="Identitas siswa">
            <div class="form-grid">
                <x-form.input name="nama_siswa" label="Nama siswa" :value="$siswa->nama_siswa" required />
                <x-form.select name="status_siswa" label="Status" :value="$siswa->status_siswa->value" :options="$opsi['status']" :placeholder="null" required />
                <x-form.select name="jenis_kelamin" label="Jenis kelamin" :value="$siswa->jenis_kelamin?->value" :options="$opsi['jenisKelamin']" required />
                <x-form.input name="nisn" label="NISN" :value="$siswa->nisn" inputmode="numeric" maxlength="10" help="10 digit angka." />
                <x-form.input name="tempat_lahir" label="Tempat lahir" :value="$siswa->tempat_lahir" required />
                <x-form.input name="tanggal_lahir" label="Tanggal lahir" type="date" :value="$siswa->tanggal_lahir?->format('Y-m-d')" required />
                <x-form.select name="agama" label="Agama" :value="$siswa->agama?->value" :options="$opsi['agama']" required />
                <x-form.select name="kebutuhan_khusus" label="Kebutuhan khusus" :value="$siswa->kebutuhan_khusus->value" :options="$opsi['kebutuhanKhusus']" :placeholder="null" required />
                <x-form.input name="no_seri_ijazah" label="No. seri ijazah" :value="$siswa->no_seri_ijazah" />
                <x-form.input name="no_seri_skhus" label="No. seri SKHUS" :value="$siswa->no_seri_skhus" />
            </div>

            <div class="form-section">Alamat dan kontak</div>
            <div class="form-grid">
                <x-form.input name="alamat_jalan" label="Alamat (jalan)" :value="$siswa->alamat_jalan" />
                <x-form.input name="desa_kelurahan" label="Desa/kelurahan" :value="$siswa->desa_kelurahan" />
                <x-form.input name="kecamatan" label="Kecamatan" :value="$siswa->kecamatan" />
                <x-form.input name="kabupaten_kota" label="Kabupaten/kota" :value="$siswa->kabupaten_kota" />
                <x-form.input name="kode_pos" label="Kode pos" :value="$siswa->kode_pos" inputmode="numeric" maxlength="5" />
                <x-form.select name="moda_transportasi" label="Moda transportasi" :value="$siswa->moda_transportasi?->value" :options="$opsi['moda']" />
                <x-form.select name="tempat_tinggal" label="Tempat tinggal" :value="$siswa->tempat_tinggal?->value" :options="$opsi['tempatTinggal']" />
                <x-form.input name="no_hp" label="No. HP (WA)" type="tel" :value="$siswa->no_hp" />
                <x-form.input name="email" label="Email" type="email" :value="$siswa->email" />
                <x-form.input name="no_kps_pkh" label="No. KPS/PKH" :value="$siswa->no_kps_pkh" />
                <x-form.input name="no_kip" label="No. KIP" :value="$siswa->no_kip" />
            </div>
        </x-admin.card>

        @foreach ($hubungan as $h)
            @php($wali = $siswa->waliDengan($h))
            @php($p = "wali[{$h->value}]")
            <x-admin.card :title="$h->label().($h->value === 'wali' ? ' (opsional)' : '')" style="margin-top:16px">
                @if ($wali && $wali->siswa()->count() > 1)
                    <x-admin.alert type="info" style="margin-bottom:12px">Data ini juga dipakai saudara kandung; perubahan berlaku untuk semuanya.</x-admin.alert>
                @endif
                <div class="form-grid">
                    <x-form.input :name="$p.'[nama_wali_siswa]'" :label="'Nama '.strtolower($h->label())" :value="$wali?->nama_wali_siswa" :required="$h->value !== 'wali'" />
                    @if ($h->value === 'wali')
                        <x-form.input :name="$p.'[hubungan_keluarga]'" label="Hubungan keluarga" :value="$wali?->pivot->hubungan_keluarga" help="Mis. paman, bibi, kakek." />
                    @endif
                    <x-form.select :name="$p.'[pendidikan]'" label="Pendidikan" :value="$wali?->pendidikan?->value" :options="$opsi['pendidikan']" />
                    <x-form.select :name="$p.'[pekerjaan]'" label="Pekerjaan" :value="$wali?->pekerjaan?->value" :options="$opsi['pekerjaan']" />
                    <x-form.select :name="$p.'[penghasilan]'" label="Penghasilan" :value="$wali?->penghasilan?->value" :options="$opsi['penghasilan']" />
                    <x-form.input :name="$p.'[no_hp]'" label="No. HP" type="tel" :value="$wali?->no_hp" />
                    <x-form.select :name="$p.'[agama]'" label="Agama" :value="$wali?->agama?->value" :options="$opsi['agama']" />
                </div>
                <x-form.textarea :name="$p.'[alamat]'" label="Alamat" :value="$wali?->alamat" rows="2" />
                @if ($h->value === 'wali')
                    <p class="form-help">Kosongkan nama untuk menghapus data wali.</p>
                @endif
            </x-admin.card>
        @endforeach

        <div style="display:flex;gap:8px;margin-top:16px">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.siswa.show', $siswa) }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
@endsection
