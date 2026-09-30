@extends('layouts.admin')

@section('title', 'Unit & Jurusan')
@section('pretitle', 'Master Data')

@section('actions')
    @can('create', App\Modules\Core\Models\UnitSekolah::class)
        <a href="{{ route('admin.unit-sekolah.create') }}" class="btn btn-primary">Tambah Unit</a>
    @endcan
@endsection

@section('content')
    <x-admin.card flush>
        <x-admin.table :empty="$units->isEmpty()" empty-text="Belum ada unit sekolah.">
            <x-slot:head>
                <tr>
                    <th>Nama Unit</th>
                    <th>Jenjang</th>
                    <th>Jurusan</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </x-slot:head>
            @foreach ($units as $unit)
                <tr>
                    <td class="cell-strong">{{ $unit->nama_unit_sekolah }}</td>
                    <td><span class="chip">{{ $unit->jenjang->label() }}</span></td>
                    <td>{{ $unit->jenjang === App\Modules\Core\Enums\Jenjang::Smk ? $unit->jurusan_count.' jurusan' : '—' }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.unit-sekolah.edit', $unit) }}" class="btn btn-outline btn-sm">Kelola</a>
                        @can('delete', $unit)
                            <x-admin.delete-form :action="route('admin.unit-sekolah.destroy', $unit)" :message="'Hapus unit '.$unit->nama_unit_sekolah.'?'" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </x-admin.card>
@endsection
