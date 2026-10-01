{{-- Beranda publik. Konten dari pengaturan situs (situs()) dan master data unit/jurusan. --}}
@extends('layouts.public')

@section('title', situs('nama'))

@section('content')
    <section id="hero" class="hero section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="hero-block">
                <div class="row align-items-center g-4 g-xl-5">
                    <div class="col-lg-6" data-aos="fade-right" data-aos-delay="100">
                        <div class="hero-copy">
                            <div class="top-badge"><i class="bi bi-mortarboard-fill"></i><span>{{ situs('nama_singkat') }}</span></div>
                            <h1>{{ situs('hero_judul', situs('nama')) }}</h1>
                            <p>{{ situs('hero_teks', situs('deskripsi')) }}</p>
                            <div class="hero-btns">
                                @if (Route::has('ppdb.index'))
                                    <a href="{{ route('ppdb.index') }}" class="btn-apply">Daftar PPDB</a>
                                @endif
                                <a href="{{ route('tentang') }}" class="btn-tour"><i class="bi bi-info-circle-fill"></i> Tentang Kami</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="200">
                        <div class="hero-visual">
                            <img src="{{ asset('img/situs/showcase-1.webp') }}" alt="Suasana sekolah" class="img-fluid campus-photo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (situs('visi'))
        <section id="about" class="about section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-5 align-items-stretch">
                    <div class="col-lg-5" data-aos="fade-right" data-aos-delay="150">
                        <div class="campus-showcase">
                            <img src="{{ asset('img/situs/campus-8.webp') }}" alt="Lingkungan sekolah" class="img-fluid" loading="lazy">
                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                        <div class="story-content">
                            <span class="subtitle">Tentang Kami</span>
                            <h2>{{ situs('nama') }}</h2>
                            <p>{{ situs('deskripsi') }}</p>
                            <div class="purpose-block mt-3">
                                <i class="bi bi-eye"></i>
                                <h4>Visi</h4>
                                <p>{{ situs('visi') }}</p>
                            </div>
                            <a href="{{ route('tentang') }}" class="feat-link mt-3 d-inline-block">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($units->isNotEmpty())
        <section id="unit-sekolah" class="featured-programs section light-background">
            <div class="container section-title" data-aos="fade-up">
                <h2>Unit Sekolah</h2>
                <p>Jenjang pendidikan di bawah naungan {{ situs('nama') }}</p>
            </div>

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-4 justify-content-center">
                    @foreach ($units as $unit)
                        <div class="col-lg-5 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + $loop->index * 100 }}">
                            <div class="program-card h-100">
                                <div class="card-thumb">
                                    <img src="{{ asset($unit->jenjang === App\Modules\Core\Enums\Jenjang::Smp ? 'img/situs/education-3.webp' : 'img/situs/education-7.webp') }}" alt="{{ $unit->nama_unit_sekolah }}" class="img-fluid" loading="lazy">
                                </div>
                                <div class="card-body-content">
                                    <span class="degree-type">{{ $unit->jenjang->label() }}</span>
                                    <h4>{{ $unit->nama_unit_sekolah }}</h4>
                                    @if ($unit->jurusan->isNotEmpty())
                                        <p class="mb-2">Program keahlian:</p>
                                        <ul class="mb-0">
                                            @foreach ($unit->jurusan as $jurusan)
                                                <li>{{ $jurusan->nama_jurusan }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mb-0">Kelas VII sampai IX.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($beritaTerbaru->isNotEmpty())
        <section id="recent-news" class="recent-news section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Berita &amp; Pengumuman</h2>
                <p>Kabar terbaru dari {{ situs('nama_singkat') }}</p>
            </div>

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row gy-4">
                    @php($utama = $beritaTerbaru->first())
                    <div class="col-lg-5" data-aos="fade-right" data-aos-delay="100">
                        <article class="featured-post">
                            <figure class="featured-img">
                                <img src="{{ $utama->urlGambar() ?? asset('img/situs/campus-5.webp') }}" alt="" class="img-fluid" loading="lazy">
                                <a href="{{ route('berita.index', ['jenis' => $utama->jenis->value]) }}" class="featured-tag">{{ $utama->jenis->label() }}</a>
                            </figure>
                            <div class="featured-body">
                                <h3 class="featured-title"><a href="{{ route('berita.show', $utama->slug) }}">{{ $utama->judul }}</a></h3>
                                <p class="featured-excerpt">{{ $utama->ringkasanTampil() }}</p>
                                <div class="featured-meta">
                                    <span class="meta-date"><i class="bi bi-clock"></i> {{ $utama->tanggal_terbit->translatedFormat('d F Y') }}</span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                        <div class="side-posts">
                            @foreach ($beritaTerbaru->skip(1) as $item)
                                <article class="side-post-item">
                                    <div class="side-post-img">
                                        <img src="{{ $item->urlGambar() ?? asset('img/situs/campus-5.webp') }}" alt="" class="img-fluid" loading="lazy">
                                    </div>
                                    <div class="side-post-content">
                                        <a href="{{ route('berita.index', ['jenis' => $item->jenis->value]) }}" class="side-tag">{{ $item->jenis->label() }}</a>
                                        <h4 class="side-post-title"><a href="{{ route('berita.show', $item->slug) }}">{{ $item->judul }}</a></h4>
                                        <div class="side-post-meta">
                                            <span class="side-date">{{ $item->tanggal_terbit->translatedFormat('d F Y') }}</span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4"><a href="{{ route('berita.index') }}" class="feat-link">Semua berita &amp; pengumuman <i class="bi bi-arrow-right"></i></a></div>
            </div>
        </section>
    @endif

    @if ($prestasiTerbaru->isNotEmpty())
        <section id="prestasi" class="section light-background">
            <div class="container section-title" data-aos="fade-up">
                <h2>Prestasi</h2>
                <p>Capaian terbaru peserta didik {{ situs('nama_singkat') }}</p>
            </div>

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-4 justify-content-center">
                    @foreach ($prestasiTerbaru as $item)
                        <div class="col-lg-4 col-md-6">
                            <article class="card h-100 shadow-sm">
                                @if ($item->gambar)
                                    <img src="{{ $item->urlGambar() }}" alt="{{ $item->judul }}" class="card-img-top" loading="lazy" style="aspect-ratio:16/9;object-fit:cover">
                                @endif
                                <div class="card-body">
                                    <div class="mb-2">
                                        <span class="badge text-bg-primary">{{ $item->tingkat->label() }}</span>
                                        <span class="badge text-bg-secondary">{{ $item->tahun }}</span>
                                    </div>
                                    <h5 class="card-title">{{ $item->judul }}</h5>
                                    <p class="card-text text-muted mb-0"><i class="bi bi-trophy"></i> {{ $item->nama_peraih }}</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4"><a href="{{ route('prestasi') }}" class="feat-link">Semua prestasi <i class="bi bi-arrow-right"></i></a></div>
            </div>
        </section>
    @endif

    @if ($galeriTerbaru->isNotEmpty())
        <section id="galeri" class="section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Galeri</h2>
                <p>Potret kegiatan di {{ situs('nama_singkat') }}</p>
            </div>

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-3">
                    @foreach ($galeriTerbaru as $foto)
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="{{ $foto->urlGambar() }}" class="glightbox d-block" data-gallery="beranda" data-title="{{ $foto->judul }}">
                                <img src="{{ $foto->urlGambarKecil() }}" alt="{{ $foto->judul }}" class="img-fluid rounded w-100" loading="lazy" style="aspect-ratio:1/1;object-fit:cover">
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-4"><a href="{{ route('galeri') }}" class="feat-link">Lihat semua foto <i class="bi bi-arrow-right"></i></a></div>
            </div>
        </section>
    @endif

    @if (Route::has('ppdb.index'))
        <section id="ajakan-ppdb" class="section">
            <div class="container text-center" data-aos="fade-up">
                <h2>Penerimaan Peserta Didik Baru</h2>
                <p class="mb-4">Daftarkan putra-putri Anda secara online. Petugas sekolah siap membantu bila Anda membutuhkan bantuan saat mengisi formulir.</p>
                <a href="{{ route('ppdb.index') }}" class="btn-apply">Lihat Informasi PPDB</a>
            </div>
        </section>
    @endif
@endsection
