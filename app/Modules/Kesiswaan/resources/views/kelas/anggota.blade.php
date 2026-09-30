@extends('layouts.admin')

@section('title', 'Anggota Kelas '.$kelas->nama_kelas)
@section('pretitle', $kelas->tahunAjaran->nama_tahun_ajaran.' · '.$kelas->unitSekolah->jenjang->label())

@section('actions')
    @can('view', $kelas)
        <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline">Kembali</a>
    @endcan
@endsection

@section('content')
    <x-admin.card>
        <div class="form-grid">
            <x-admin.info label="Kelas">{{ $kelas->nama_kelas }}</x-admin.info>
            <x-admin.info label="Jurusan">{{ $kelas->jurusan?->nama_jurusan }}</x-admin.info>
            <x-admin.info label="Wali kelas">{{ $kelas->waliKelas?->nama_pegawai }}</x-admin.info>
            <x-admin.info label="Jumlah siswa">{{ $anggota->count() }}</x-admin.info>
        </div>
    </x-admin.card>

    <x-admin.card flush style="margin-top:16px">
        <x-admin.table :empty="$anggota->isEmpty()" empty-text="Belum ada siswa di kelas ini.">
            <x-slot:head>
                <tr><th style="width:48px">No</th><th>Nama</th><th>NISN</th><th>L/P</th><th style="text-align:right">Aksi</th></tr>
            </x-slot:head>
            @foreach ($anggota as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="cell-strong">{{ $item->siswa->nama_siswa }}</td>
                    <td>{{ $item->siswa->nisn ?? '—' }}</td>
                    <td>{{ $item->siswa->jenis_kelamin ? strtoupper($item->siswa->jenis_kelamin->value) : '—' }}</td>
                    <td style="text-align:right">
                        @can('view', $item->siswa)
                            <a href="{{ route('admin.siswa.show', $item->siswa) }}" class="btn btn-outline btn-sm">Detail</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </x-admin.card>
@endsection
