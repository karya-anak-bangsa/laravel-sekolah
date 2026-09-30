@extends('layouts.admin')

@section('title', 'Ganti Kata Sandi')
@section('pretitle', 'Akun Saya')

@section('content')
    <x-admin.card title="Ganti kata sandi" subtitle="Minimal 8 karakter, mengandung huruf dan angka." style="max-width:520px">
        <form method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            @method('PUT')

            <x-form.input name="password_lama" label="Kata sandi lama" type="password" autocomplete="current-password" required />
            <x-form.input name="password" label="Kata sandi baru" type="password" autocomplete="new-password" required />
            <x-form.input name="password_confirmation" label="Ulangi kata sandi baru" type="password" autocomplete="new-password" required />

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </x-admin.card>
@endsection
