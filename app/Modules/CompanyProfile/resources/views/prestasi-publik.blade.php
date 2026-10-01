@extends('layouts.public')

@section('title', 'Prestasi')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Prestasi</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="current">Prestasi</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
                <a href="{{ route('prestasi') }}" @class(['btn', 'btn-sm', $tingkat ? 'btn-outline-primary' : 'btn-primary'])>Semua</a>
                @foreach (App\Modules\CompanyProfile\Enums\TingkatPrestasi::cases() as $opsi)
                    <a href="{{ route('prestasi', ['tingkat' => $opsi->value]) }}" @class(['btn', 'btn-sm', $tingkat === $opsi ? 'btn-primary' : 'btn-outline-primary'])>{{ $opsi->label() }}</a>
                @endforeach
            </div>

            @forelse ($prestasi as $item)
                @if ($loop->first)<div class="row g-4">@endif
                <div class="col-lg-4 col-md-6">
                    <article class="card h-100 shadow-sm">
                        @if ($item->gambar)
                            <img src="{{ $item->urlGambar() }}" alt="{{ $item->judul }}" class="card-img-top" loading="lazy" style="aspect-ratio:16/9;object-fit:cover">
                        @endif
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge text-bg-primary">{{ $item->tingkat->label() }}</span>
                                <span class="badge text-bg-secondary">{{ $item->tahun }}</span>
                                @if ($item->unitSekolah)<span class="badge text-bg-light border">{{ $item->unitSekolah->jenjang->label() }}</span>@endif
                            </div>
                            <h5 class="card-title">{{ $item->judul }}</h5>
                            <p class="card-text text-muted mb-0"><i class="bi bi-trophy"></i> {{ $item->nama_peraih }}</p>
                        </div>
                    </article>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <p class="text-center text-muted py-5">Belum ada prestasi{{ $tingkat ? ' pada tingkat ini' : '' }}.</p>
            @endforelse

            <div class="mt-4 d-flex justify-content-center">{{ $prestasi->links('pagination::bootstrap-5') }}</div>
        </div>
    </section>
@endsection
