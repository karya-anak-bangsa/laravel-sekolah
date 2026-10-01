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
