@extends('layouts.public')

@section('title', 'Tentang Kami')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Tentang Kami</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="current">Tentang</li>
                </ol>
            </nav>
        </div>
    </div>

    @if ($paragrafSejarah)
        <section id="sejarah" class="about section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Sejarah</h2>
            </div>
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-5">
                        <div class="campus-showcase">
                            <img src="{{ asset('img/situs/campus-7.webp') }}" alt="Gedung sekolah" class="img-fluid" loading="lazy">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        @foreach ($paragrafSejarah as $paragraf)
                            <p>{{ $paragraf }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (situs('visi') || $butirMisi)
        <section id="visi-misi" class="section light-background">
            <div class="container section-title" data-aos="fade-up">
                <h2>Visi &amp; Misi</h2>
            </div>
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-4">
                    @if (situs('visi'))
                        <div class="col-lg-5">
                            <div class="purpose-block h-100">
                                <i class="bi bi-eye"></i>
                                <h4>Visi</h4>
                                <p>{{ situs('visi') }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($butirMisi)
                        <div class="col-lg-7">
                            <div class="purpose-block h-100">
                                <i class="bi bi-bullseye"></i>
                                <h4>Misi</h4>
                                <ol class="mb-0 ps-3">
                                    @foreach ($butirMisi as $butir)
                                        <li class="mb-2">{{ $butir }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($pengurus->isNotEmpty())
        <section id="struktur-organisasi" class="section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Struktur Organisasi</h2>
            </div>
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                @foreach ($pengurus as $kelompok => $anggota)
                    <h4 class="text-center mb-3 {{ $loop->first ? '' : 'mt-5' }}">{{ $kelompok }}</h4>
                    <div class="row g-3 justify-content-center">
                        @foreach ($anggota as $orang)
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="info-tile h-100">
                                    <div class="tile-icon"><i class="bi bi-person"></i></div>
                                    <h5>{{ $orang->nama_pengurus }}</h5>
                                    <p class="mb-0">{{ $orang->jabatan }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
