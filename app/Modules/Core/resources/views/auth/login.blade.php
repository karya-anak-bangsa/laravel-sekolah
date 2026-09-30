@extends('layouts.admin-auth')

@section('title', 'Masuk')

@section('content')
    <div class="auth-title">Masuk ke Panel Admin</div>
    <div class="auth-subtitle">Gunakan username dan kata sandi pegawai Anda.</div>

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        <x-form.input name="username" label="Username" autocomplete="username" autofocus required />
        <x-form.input name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

        <div class="auth-actions">
            <label class="form-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px">Masuk</button>
    </form>

    <div class="auth-footer">Lupa kata sandi? Hubungi petugas TU atau administrator sekolah.</div>
@endsection
