@extends('layouts.admin')

@section('title', 'Pengaturan Situs')
@section('pretitle', 'Situs')

@section('content')
    <form method="POST" action="{{ route('admin.pengaturan.update') }}">
        @csrf
        @method('PUT')

        @foreach ($kelompok as $idKelompok => $isian)
            <x-admin.card :title="$judulKelompok[$idKelompok]" style="margin-bottom:16px">
                @foreach ($isian as $kunci => $definisi)
                    @if ($definisi['jenis'] === 'textarea')
                        <x-form.textarea :name="$kunci" :label="$definisi['label']" :value="$nilai[$kunci]" :help="$definisi['bantuan']" :rows="$kunci === 'sejarah' ? 10 : 4" />
                    @else
                        <x-form.input :name="$kunci" :label="$definisi['label']" :type="$definisi['jenis']" :value="$nilai[$kunci]" :help="$definisi['bantuan']" :required="in_array('required', $definisi['aturan'])" />
                    @endif
                @endforeach
            </x-admin.card>
        @endforeach

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection
