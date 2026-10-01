@extends('layouts.public')

@section('title', $berita->judul)
@section('description', $berita->ringkasanTampil(155))

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0 fs-2">{{ $berita->jenis->label() }}</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('berita.index') }}">Berita</a></li>
                    <li class="current">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    <article>
                        <h2 class="mb-2">{{ $berita->judul }}</h2>
                        <div class="text-muted mb-4">
                            <span class="badge text-bg-secondary">{{ $berita->jenis->label() }}</span>
                            <span class="ms-2"><i class="bi bi-clock"></i> {{ $berita->tanggal_terbit->translatedFormat('d F Y') }}</span>
                        </div>

                        @if ($berita->gambar)
                            <img src="{{ $berita->urlGambar() }}" alt="" class="img-fluid rounded mb-4 w-100">
                        @endif

                        @foreach ($berita->paragraf() as $paragraf)
                            <p>{!! nl2br(e($paragraf)) !!}</p>
                        @endforeach
                    </article>
                </div>

                <div class="col-lg-4">
                    <aside>
                        <h5 class="mb-3">Berita Lainnya</h5>
                        @forelse ($lainnya as $item)
                            <div class="mb-3">
                                <a href="{{ route('berita.show', $item->slug) }}" class="fw-semibold text-decoration-none">{{ $item->judul }}</a>
                                <div class="small text-muted">{{ $item->tanggal_terbit->translatedFormat('d F Y') }}</div>
                            </div>
                        @empty
                            <p class="text-muted">Belum ada berita lainnya.</p>
                        @endforelse
                        <a href="{{ route('berita.index') }}">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection
