@extends('layouts.public')

@section('title', 'Kontak')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Kontak</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="current">Kontak</li>
                </ol>
            </nav>
        </div>
    </div>

    <section id="contact" class="contact section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Hubungi Kami</h2>
            <p>{{ situs('nama') }}</p>
        </div>

        <div class="container" data-aos="fade-up" data-aos-delay="100">
            @php
                $info = array_filter([
                    ['bi-geo-alt', 'Alamat', situs('alamat')],
                    ['bi-envelope', 'Email', situs('email')],
                    ['bi-telephone', 'Telepon', situs('telepon')],
                    ['bi-clock', 'Jam Layanan', situs('jam_layanan')],
                ], fn (array $item) => filled($item[2]));
            @endphp

            <div class="row gy-4 mb-5">
                @foreach ($info as [$ikon, $judul, $isi])
                    <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="{{ 100 + $loop->index * 100 }}">
                        <div class="info-tile h-100">
                            <div class="tile-icon"><i class="bi {{ $ikon }}"></i></div>
                            <h5>{{ $judul }}</h5>
                            <p>{{ $isi }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (situs('peta_embed_url'))
                <div class="map-block" style="min-height:400px">
                    <iframe src="{{ situs('peta_embed_url') }}" width="100%" height="400" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi sekolah"></iframe>
                </div>
            @endif
        </div>
    </section>
@endsection
