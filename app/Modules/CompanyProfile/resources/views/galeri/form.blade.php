@extends('layouts.admin')

@php($ada = $galeri->exists)

@section('title', $ada ? 'Ubah Foto' : 'Tambah Foto')
@section('pretitle', 'Galeri Foto')

@section('content')
    <x-admin.card>
        <form method="POST" enctype="multipart/form-data" action="{{ $ada ? route('admin.galeri.update', $galeri) : route('admin.galeri.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="judul" label="Judul / keterangan foto" :value="$galeri->judul" maxlength="150" required />

            <x-form.field name="gambar" :label="$ada ? 'Ganti foto' : 'Foto'" :required="! $ada" help="JPG, PNG, atau WebP, maksimal 4 MB. Otomatis dikecilkan dan dikompres.">
                @if ($ada)
                    <img src="{{ $galeri->urlGambarKecil() }}" alt="" style="max-width:240px;border-radius:6px;display:block;margin-bottom:8px">
                @endif
                <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="form-control" @required(! $ada)>
            </x-form.field>

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.galeri.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
