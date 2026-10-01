@extends('layouts.admin')

@php($ada = $prestasi->exists)

@section('title', $ada ? 'Ubah Prestasi' : 'Tambah Prestasi')
@section('pretitle', 'Prestasi')

@section('content')
    <x-admin.card>
        <form method="POST" enctype="multipart/form-data" action="{{ $ada ? route('admin.prestasi.update', $prestasi) : route('admin.prestasi.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.input name="judul" label="Prestasi" :value="$prestasi->judul" placeholder="mis. Juara 1 Lomba Web Design" maxlength="200" required />
            <x-form.input name="nama_peraih" label="Nama peraih" :value="$prestasi->nama_peraih" help="Nama siswa, tim, atau sekolah." maxlength="150" required />

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                <x-form.select name="tingkat" label="Tingkat" :value="$prestasi->tingkat?->value" :options="$tingkat" required />
                <x-form.input name="tahun" label="Tahun" type="number" :value="$prestasi->tahun" min="2000" required />
                <x-form.select name="id_unit_sekolah" label="Unit" :value="$prestasi->id_unit_sekolah" :options="$units" placeholder="Yayasan (semua unit)" />
            </div>

            <x-form.field name="gambar" label="Foto" help="Opsional. JPG, PNG, atau WebP, maksimal 4 MB. Otomatis dikecilkan dan dikompres.">
                @if ($prestasi->gambar)
                    <div style="margin-bottom:8px">
                        <img src="{{ $prestasi->urlGambar() }}" alt="" style="max-width:240px;border-radius:6px;display:block">
                        <label style="display:inline-flex;gap:6px;align-items:center;margin-top:6px">
                            <input type="checkbox" name="hapus_gambar" value="1" @checked(old('hapus_gambar'))> Hapus foto ini
                        </label>
                    </div>
                @endif
                <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" class="form-control">
            </x-form.field>

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.prestasi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
