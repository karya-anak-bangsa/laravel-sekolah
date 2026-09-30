@extends('layouts.admin')

@php($ada = $pegawai->exists)

@section('title', $ada ? 'Ubah Pegawai' : 'Tambah Pegawai')
@section('pretitle', 'Pegawai')

@section('content')
    <x-admin.card>
        <form method="POST" action="{{ $ada ? route('admin.pegawai.update', $pegawai) : route('admin.pegawai.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="nama_pegawai" label="Nama pegawai" :value="$pegawai->nama_pegawai" required />
            <x-form.select name="jenis_pegawai" label="Jenis pegawai" :value="$pegawai->jenis_pegawai?->value"
                :options="collect($jenis)->mapWithKeys(fn ($j) => [$j->value => $j->label()])->all()"
                help="Jabatan (kepala sekolah, wali kelas, dst.) diatur lewat role dan penugasan, bukan di sini." required />
            <x-form.select name="id_unit_sekolah" label="Unit sekolah" :value="$pegawai->id_unit_sekolah ?? ($units->count() === 1 ? $units->first()->id_unit_sekolah : null)"
                placeholder="— Tingkat yayasan —" :options="$units->pluck('nama_unit_sekolah', 'id_unit_sekolah')->all()"
                help="Kosongkan hanya untuk pimpinan yayasan." />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
