{{-- Placeholder beranda; konten company profile dibuat di Fase 2. --}}
@extends('layouts.public')

@section('title', config('sekolah.nama'))

@section('content')
    <section class="section">
        <div class="container text-center py-5">
            <h2>{{ config('sekolah.nama') }}</h2>
            <p class="lead">{{ config('sekolah.deskripsi') }}</p>
        </div>
    </section>
@endsection
