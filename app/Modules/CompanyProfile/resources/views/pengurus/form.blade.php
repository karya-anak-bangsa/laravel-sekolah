@extends('layouts.admin')

@php($ada = $pengurus->exists)

@section('title', $ada ? 'Ubah Pengurus' : 'Tambah Pengurus')
@section('pretitle', 'Struktur Organisasi')

@section('content')
    <x-admin.card>
        <form method="POST" action="{{ $ada ? route('admin.pengurus.update', $pengurus) : route('admin.pengurus.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="nama_pengurus" label="Nama" :value="$pengurus->nama_pengurus" required />
            <x-form.input name="jabatan" label="Jabatan" :value="$pengurus->jabatan" placeholder="mis. Ketua Yayasan, Kepala Sekolah" required />
            <x-form.select name="id_unit_sekolah" label="Tingkat" :value="$pengurus->id_unit_sekolah" :options="$units" placeholder="Yayasan" help="Pilih unit bila pengurus ini bagian dari SMP/SMK; kosongkan untuk tingkat yayasan." />
            <x-form.input name="urutan" label="Urutan tampil" type="number" :value="$pengurus->urutan ?? 0" min="0" max="999" help="Angka kecil tampil lebih dulu di dalam tingkatnya." />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.pengurus.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
