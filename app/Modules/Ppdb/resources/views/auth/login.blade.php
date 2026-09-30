@extends('layouts.public')

@section('title', 'Masuk PPDB')
@section('body-class', 'starter-page')

@section('content')
    <section class="section">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-1">Masuk Pendaftar PPDB</h2>
                            <p class="text-muted mb-4">Gunakan nomor HP dan kata sandi yang Anda daftarkan.</p>

                            <form method="POST" action="{{ route('ppdb.login.store') }}">
                                @csrf

                                <x-form.input theme="public" name="no_hp" label="Nomor HP" type="tel" inputmode="numeric" autocomplete="tel" autofocus required />
                                <x-form.input theme="public" name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                                    <label class="form-check-label" for="remember">Ingat saya</label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Masuk</button>
                            </form>

                            <p class="small text-muted mt-3 mb-0">Lupa kata sandi? Hubungi petugas TU sekolah.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
