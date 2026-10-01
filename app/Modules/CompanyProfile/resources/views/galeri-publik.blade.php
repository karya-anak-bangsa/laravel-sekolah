@extends('layouts.public')

@section('title', 'Galeri')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Galeri</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="current">Galeri</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            @if ($galeri->isEmpty())
                <p class="text-center text-muted py-5">Belum ada foto di galeri.</p>
            @else
                <div class="row g-3">
                    @foreach ($galeri as $foto)
                        <div class="col-lg-3 col-md-4 col-6">
                            <a href="{{ $foto->urlGambar() }}" class="glightbox d-block" data-gallery="galeri" data-title="{{ $foto->judul }}">
                                <img src="{{ $foto->urlGambarKecil() }}" alt="{{ $foto->judul }}" class="img-fluid rounded w-100" loading="lazy" style="aspect-ratio:4/3;object-fit:cover">
                            </a>
                            <div class="small text-muted mt-1">{{ $foto->judul }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex justify-content-center">{{ $galeri->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </section>
@endsection
