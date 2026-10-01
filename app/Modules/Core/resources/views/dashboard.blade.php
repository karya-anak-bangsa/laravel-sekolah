@extends('layouts.admin')

@section('title', 'Dashboard')
@section('pretitle', 'Ringkasan')

@section('content')
    <x-admin.card title="Selamat datang, {{ auth()->user()->nama }}" subtitle="Sistem Akademik Sekolah {{ situs('nama') }}">
        <p>Pilih menu di sebelah kiri untuk mulai bekerja. Menu yang tampil disesuaikan dengan hak akses Anda.</p>
    </x-admin.card>
@endsection
