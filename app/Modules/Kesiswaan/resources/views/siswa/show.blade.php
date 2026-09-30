@extends('layouts.admin')

@php
    use App\Modules\Kesiswaan\Enums\HubunganWali;
    $tanggal = fn ($t) => $t?->format('d/m/Y');
@endphp

@section('title', $siswa->nama_siswa)
@section('pretitle', 'Siswa')

@section('actions')
    <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline">Kembali</a>
    @can('update', $siswa)
        <a href="{{ route('admin.siswa.edit', $siswa) }}" class="btn btn-primary">Ubah Data</a>
    @endcan
@endsection

@section('content')
    <x-admin.card title="Identitas siswa">
        <div class="form-grid">
            <x-admin.info label="Nama">{{ $siswa->nama_siswa }}</x-admin.info>
            <x-admin.info label="Status">{{ $siswa->status_siswa->label() }}</x-admin.info>
            <x-admin.info label="Unit">{{ $siswa->unitSekolah->nama_unit_sekolah }}</x-admin.info>
            <x-admin.info label="Jenis kelamin">{{ $siswa->jenis_kelamin?->label() }}</x-admin.info>
            <x-admin.info label="NISN">{{ $siswa->nisn }}</x-admin.info>
            <x-admin.info label="Tempat, tanggal lahir">{{ collect([$siswa->tempat_lahir, $tanggal($siswa->tanggal_lahir)])->filter()->implode(', ') }}</x-admin.info>
            <x-admin.info label="Agama">{{ $siswa->agama?->label() }}</x-admin.info>
            <x-admin.info label="Kebutuhan khusus">{{ $siswa->kebutuhan_khusus->label() }}</x-admin.info>
            <x-admin.info label="No. seri ijazah">{{ $siswa->no_seri_ijazah }}</x-admin.info>
            <x-admin.info label="No. seri SKHUS">{{ $siswa->no_seri_skhus }}</x-admin.info>
            <x-admin.info label="Alamat">{{ collect([$siswa->alamat_jalan, $siswa->desa_kelurahan, $siswa->kecamatan, $siswa->kabupaten_kota, $siswa->kode_pos])->filter()->implode(', ') }}</x-admin.info>
            <x-admin.info label="Moda transportasi">{{ $siswa->moda_transportasi?->label() }}</x-admin.info>
            <x-admin.info label="Tempat tinggal">{{ $siswa->tempat_tinggal?->label() }}</x-admin.info>
            <x-admin.info label="No. HP">{{ $siswa->no_hp }}</x-admin.info>
            <x-admin.info label="Email">{{ $siswa->email }}</x-admin.info>
            <x-admin.info label="No. KPS/PKH">{{ $siswa->no_kps_pkh }}</x-admin.info>
            <x-admin.info label="No. KIP">{{ $siswa->no_kip }}</x-admin.info>
        </div>
    </x-admin.card>

    @foreach (HubunganWali::cases() as $hubungan)
        @php($wali = $siswa->waliDengan($hubungan))
        @continue($hubungan === HubunganWali::Wali && ! $wali)
        <x-admin.card :title="$hubungan->label()" style="margin-top:16px">
            @if ($wali)
                <div class="form-grid">
                    <x-admin.info label="Nama">{{ $wali->nama_wali_siswa }}</x-admin.info>
                    @if ($hubungan === HubunganWali::Wali)
                        <x-admin.info label="Hubungan keluarga">{{ $wali->pivot->hubungan_keluarga }}</x-admin.info>
                    @endif
                    <x-admin.info label="Pendidikan">{{ $wali->pendidikan?->label() }}</x-admin.info>
                    <x-admin.info label="Pekerjaan">{{ $wali->pekerjaan?->label() }}</x-admin.info>
                    <x-admin.info label="Penghasilan">{{ $wali->penghasilan?->label() }}</x-admin.info>
                    <x-admin.info label="No. HP">{{ $wali->no_hp }}</x-admin.info>
                    <x-admin.info label="Agama">{{ $wali->agama?->label() }}</x-admin.info>
                    <x-admin.info label="Alamat">{{ $wali->alamat }}</x-admin.info>
                </div>
            @else
                <p style="color:var(--text-muted);margin:0">Belum diisi.</p>
            @endif
        </x-admin.card>
    @endforeach

    <x-admin.card title="Kelas" style="margin-top:16px">
        @forelse ($siswa->anggotaKelas as $anggota)
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                <span>
                    <strong>{{ $anggota->kelas?->nama_kelas }}</strong>
                    <span style="color:var(--text-muted)"> — {{ $anggota->kelas?->tahunAjaran?->nama_tahun_ajaran }}</span>
                </span>
                @can('update', $siswa)
                    <x-admin.delete-form :action="route('admin.siswa.kelas.destroy', [$siswa, $anggota])" label="Keluarkan" message="Keluarkan siswa dari kelas ini?" />
                @endcan
            </div>
        @empty
            <p style="color:var(--text-muted)">Belum ditempatkan di kelas.</p>
        @endforelse

        @can('update', $siswa)
            <form method="POST" action="{{ route('admin.siswa.kelas.store', $siswa) }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-top:12px">
                @csrf
                <div style="min-width:240px;flex:1;max-width:360px">
                    <x-form.select name="id_kelas" label="Tempatkan / pindahkan ke kelas" :options="$pilihanKelas->pluck('nama_kelas', 'id_kelas')->all()"
                        help="Kelas tahun ajaran aktif di unit siswa ini. Bila sudah punya kelas di tahun yang sama, siswa dipindahkan." />
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom:16px">Simpan</button>
            </form>
        @endcan
    </x-admin.card>
@endsection
