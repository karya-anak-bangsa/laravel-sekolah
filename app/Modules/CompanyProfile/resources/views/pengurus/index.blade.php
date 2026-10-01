@extends('layouts.admin')

@section('title', 'Struktur Organisasi')
@section('pretitle', 'Situs')

@section('actions')
    @can('create', App\Modules\CompanyProfile\Models\Pengurus::class)
        <a href="{{ route('admin.pengurus.create') }}" class="btn btn-primary">Tambah Pengurus</a>
    @endcan
@endsection

@section('content')
    <x-admin.card flush>
        <x-admin.table :empty="$pengurus->isEmpty()" empty-text="Belum ada pengurus. Bagian struktur organisasi di situs publik akan kosong.">
            <x-slot:head>
                <tr>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Tingkat</th>
                    <th>Urutan</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($pengurus as $item)
                <tr>
                    <td class="cell-strong">{{ $item->nama_pengurus }}</td>
                    <td>{{ $item->jabatan }}</td>
                    <td>{{ $item->unitSekolah?->nama_unit_sekolah ?? 'Yayasan' }}</td>
                    <td>{{ $item->urutan }}</td>
                    <td style="text-align:right">
                        @can('update', $item)
                            <a href="{{ route('admin.pengurus.edit', $item) }}" class="btn btn-outline btn-sm">Ubah</a>
                        @endcan
                        @can('delete', $item)
                            <x-admin.delete-form :action="route('admin.pengurus.destroy', $item)" :message="'Hapus '.$item->nama_pengurus.' dari struktur organisasi?'" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </x-admin.card>
@endsection
