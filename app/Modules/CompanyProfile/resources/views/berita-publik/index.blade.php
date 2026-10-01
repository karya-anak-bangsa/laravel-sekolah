@extends('layouts.public')

@section('title', $jenis ? $jenis->label() : 'Berita & Pengumuman')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">{{ $jenis ? $jenis->label() : 'Berita & Pengumuman' }}</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="current">Berita</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
                <a href="{{ route('berita.index') }}" @class(['btn', 'btn-sm', $jenis ? 'btn-outline-primary' : 'btn-primary'])>Semua</a>
                @foreach (App\Modules\CompanyProfile\Enums\JenisBerita::cases() as $opsi)
                    <a href="{{ route('berita.index', ['jenis' => $opsi->value]) }}" @class(['btn', 'btn-sm', $jenis === $opsi ? 'btn-primary' : 'btn-outline-primary'])>{{ $opsi->label() }}</a>
                @endforeach
            </div>

            @forelse ($berita as $item)
                @if ($loop->first)<div class="row g-4">@endif
                <div class="col-lg-4 col-md-6">
                    <article class="card h-100 shadow-sm">
                        <a href="{{ route('berita.show', $item->slug) }}">
                            <img src="{{ $item->urlGambar() ?? asset('img/situs/campus-5.webp') }}" alt="" class="card-img-top" loading="lazy" style="aspect-ratio:16/9;object-fit:cover">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <div class="small text-muted mb-2">
                                <span class="badge text-bg-secondary">{{ $item->jenis->label() }}</span>
                                <span class="ms-1"><i class="bi bi-clock"></i> {{ $item->tanggal_terbit->translatedFormat('d F Y') }}</span>
                            </div>
                            <h5 class="card-title"><a href="{{ route('berita.show', $item->slug) }}" class="text-reset text-decoration-none">{{ $item->judul }}</a></h5>
                            <p class="card-text text-muted">{{ $item->ringkasanTampil() }}</p>
                            <a href="{{ route('berita.show', $item->slug) }}" class="mt-auto">Baca selengkapnya <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <p class="text-center text-muted py-5">Belum ada {{ $jenis ? strtolower($jenis->label()) : 'berita atau pengumuman' }}.</p>
            @endforelse

            <div class="mt-4 d-flex justify-content-center">{{ $berita->links('pagination::bootstrap-5') }}</div>
        </div>
    </section>
@endsection
