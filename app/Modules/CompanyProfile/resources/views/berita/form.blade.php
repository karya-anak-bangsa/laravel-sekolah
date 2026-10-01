@extends('layouts.admin')

@php($ada = $berita->exists)

@section('title', $ada ? 'Ubah Berita' : 'Tulis Berita')
@section('pretitle', 'Berita & Pengumuman')

@section('content')
    <x-admin.card>
        <form method="POST" enctype="multipart/form-data" action="{{ $ada ? route('admin.berita.update', $berita) : route('admin.berita.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.select name="jenis" label="Jenis" :value="$berita->jenis?->value" :options="$jenis" :placeholder="null" required />
            <x-form.input name="judul" label="Judul" :value="$berita->judul" maxlength="200" required />
            <x-form.textarea name="ringkasan" label="Ringkasan" :value="$berita->ringkasan" rows="2" maxlength="300" help="Opsional, maksimal 300 karakter. Bila kosong, diambil dari awal isi." />
            <x-form.textarea name="isi" label="Isi" :value="$berita->isi" rows="12" help="Teks biasa. Pisahkan paragraf dengan satu baris kosong." required />

            <x-form.field name="gambar" label="Gambar" help="Opsional. JPG, PNG, atau WebP, maksimal 4 MB. Otomatis dikecilkan dan dikompres.">
                @if ($berita->gambar)
                    <div style="margin-bottom:8px">
                        <img src="{{ $berita->urlGambar() }}" alt="" style="max-width:240px;border-radius:6px;display:block">
                        <label style="display:inline-flex;gap:6px;align-items:center;margin-top:6px">
                            <input type="checkbox" name="hapus_gambar" value="1" @checked(old('hapus_gambar'))> Hapus gambar ini
                        </label>
                    </div>
                @endif
                <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="form-control">
            </x-form.field>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px">
                <x-form.select name="status" label="Status" :value="$berita->status?->value" :options="$status" :placeholder="null" help="Draf tidak tampil di situs." required />
                <x-form.input name="tanggal_terbit" label="Tanggal terbit" type="datetime-local" :value="$berita->tanggal_terbit?->format('Y-m-d\TH:i')" help="Kosong = sekarang. Tanggal mendatang = tayang terjadwal." />
            </div>

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.berita.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
