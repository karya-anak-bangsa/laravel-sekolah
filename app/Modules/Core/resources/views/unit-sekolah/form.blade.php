@extends('layouts.admin')

@php($ada = $unit->exists)

@section('title', $ada ? 'Kelola Unit' : 'Tambah Unit')
@section('pretitle', 'Unit & Jurusan')

@section('content')
    <x-admin.card :title="$ada ? 'Data Unit' : null">
        <form method="POST" action="{{ $ada ? route('admin.unit-sekolah.update', $unit) : route('admin.unit-sekolah.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="nama_unit_sekolah" label="Nama unit sekolah" :value="$unit->nama_unit_sekolah" required />
            <x-form.select name="jenjang" label="Jenjang" :value="$unit->jenjang?->value" :options="collect($jenjang)->mapWithKeys(fn ($j) => [$j->value => $j->label()])->all()" required />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.unit-sekolah.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>

    @if ($ada && $unit->jenjang === App\Modules\Core\Enums\Jenjang::Smk)
        <x-admin.card title="Jurusan" subtitle="Jurusan yang tersedia di unit ini." flush style="margin-top:16px">
            <x-slot:options>
                @can('create', App\Modules\Core\Models\Jurusan::class)
                    <a href="{{ route('admin.jurusan.create', $unit) }}" class="btn btn-primary btn-sm">Tambah Jurusan</a>
                @endcan
            </x-slot:options>

            <x-admin.table :empty="$unit->jurusan->isEmpty()" empty-text="Belum ada jurusan.">
                <x-slot:head>
                    <tr><th>Kode</th><th>Nama Jurusan</th><th style="text-align:right">Aksi</th></tr>
                </x-slot:head>
                @foreach ($unit->jurusan->sortBy('nama_jurusan') as $jurusan)
                    <tr>
                        <td><span class="chip">{{ $jurusan->kode_jurusan }}</span></td>
                        <td class="cell-strong">{{ $jurusan->nama_jurusan }}</td>
                        <td style="text-align:right">
                            @can('update', $jurusan)
                                <a href="{{ route('admin.jurusan.edit', $jurusan) }}" class="btn btn-outline btn-sm">Ubah</a>
                            @endcan
                            @can('delete', $jurusan)
                                <x-admin.delete-form :action="route('admin.jurusan.destroy', $jurusan)" :message="'Hapus jurusan '.$jurusan->nama_jurusan.'?'" />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>
        </x-admin.card>
    @endif
@endsection
