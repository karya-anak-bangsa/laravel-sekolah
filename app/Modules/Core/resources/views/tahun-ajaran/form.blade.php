@extends('layouts.admin')

@php($ada = $tahunAjaran->exists)

@section('title', $ada ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran')
@section('pretitle', 'Tahun Ajaran')

@section('content')
    <x-admin.card>
        <form method="POST" action="{{ $ada ? route('admin.tahun-ajaran.update', $tahunAjaran) : route('admin.tahun-ajaran.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="nama_tahun_ajaran" label="Tahun ajaran" :value="$tahunAjaran->nama_tahun_ajaran" placeholder="2026/2027" help="Format 2026/2027 (tahun berurutan)." required />
            <x-form.select name="semester_aktif" label="Semester" :value="$tahunAjaran->semester_aktif?->value" :options="collect($semester)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" help="Ubah semester di sini saat pergantian ganjil/genap." required />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
