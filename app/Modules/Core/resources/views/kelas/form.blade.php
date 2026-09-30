@extends('layouts.admin')

@php($ada = $kelas->exists)

@section('title', $ada ? 'Ubah Kelas' : 'Tambah Kelas')
@section('pretitle', 'Kelas')

@section('content')
    <x-admin.card>
        <form method="POST" action="{{ $ada ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}">
            @csrf
            @if ($ada) @method('PUT') @endif

            <x-form.select name="id_unit_sekolah" label="Unit sekolah" :value="$kelas->id_unit_sekolah ?? ($units->count() === 1 ? $units->first()->id_unit_sekolah : null)"
                :options="$units->pluck('nama_unit_sekolah', 'id_unit_sekolah')->all()" required />
            <x-form.select name="id_tahun_ajaran" label="Tahun ajaran" :value="$kelas->id_tahun_ajaran"
                :options="$tahunAjaran->pluck('nama_tahun_ajaran', 'id_tahun_ajaran')->all()" required />
            <x-form.select name="tingkat" label="Tingkat" :value="$kelas->tingkat" :options="collect(range(7, 12))->mapWithKeys(fn ($t) => [$t => $t])->all()"
                help="SMP: 7–9, SMK: 10–12." required />
            <x-form.select name="id_jurusan" label="Jurusan" :value="$kelas->id_jurusan" placeholder="— Tanpa jurusan (SMP) —"
                :options="$jurusan->mapWithKeys(fn ($j) => [$j->id_jurusan => $j->kode_jurusan.' — '.$j->nama_jurusan])->all()"
                help="Wajib untuk kelas SMK, kosongkan untuk SMP." />
            <x-form.input name="nama_kelas" label="Nama kelas" :value="$kelas->nama_kelas" placeholder="mis. X RPL 1 atau VII-A" required />
            <x-form.select name="id_pegawai_wali_kelas" label="Wali kelas" :value="$kelas->id_pegawai_wali_kelas" placeholder="— Belum ditentukan —"
                :options="$guru->mapWithKeys(fn ($g) => [$g->id_pegawai => $g->nama_pegawai.' ('.$g->unitSekolah?->jenjang->label().')'])->all()"
                help="Hanya guru pada unit yang sama." />

            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </x-admin.card>
@endsection
