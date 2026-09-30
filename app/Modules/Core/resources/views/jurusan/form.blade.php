@extends('layouts.admin')

@php($ada = $jurusan->exists)

@section('title', $ada ? 'Ubah Jurusan' : 'Tambah Jurusan')
@section('pretitle', $unit->nama_unit_sekolah)

@section('content')
    <x-admin.card>
        <form method="POST" action="{{ $ada ? route('admin.jurusan.update', $jurusan) : route('admin.jurusan.store', $unit) }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="kode_jurusan" label="Kode jurusan" :value="$jurusan->kode_jurusan" help="Singkatan, mis. RPL. Otomatis huruf besar." required />
            <x-form.input name="nama_jurusan" label="Nama jurusan" :value="$jurusan->nama_jurusan" required />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.unit-sekolah.edit', $unit) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
