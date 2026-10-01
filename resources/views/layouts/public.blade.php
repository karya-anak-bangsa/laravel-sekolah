{{--
    Layout situs publik (UniPulse, Bootstrap 5): company profile dan area pendaftar PPDB.
    Section: title, description, content (wajib). Stack: head, scripts.
--}}
@php($menu = \App\Support\Menu::publik())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', situs('nama')) | {{ situs('nama_singkat') }}</title>
    <meta name="description" content="@yield('description', situs('deskripsi'))">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/scss/public.scss', 'resources/js/public.js'])
    @stack('head')
</head>
<body class="@yield('body-class', 'index-page')">

<header id="header" class="header position-relative">
    <div class="container">
        @if (situs('email') || situs('telepon'))
            <div class="header-top d-flex align-items-center justify-content-between">
                <div class="contact-info d-none d-lg-flex align-items-center">
                    @if (situs('email'))
                        <i class="bi bi-envelope"></i>
                        <a href="mailto:{{ situs('email') }}">{{ situs('email') }}</a>
                    @endif
                    @if (situs('telepon'))
                        <i class="bi bi-phone ms-4"></i>
                        <span>{{ situs('telepon') }}</span>
                    @endif
                </div>
            </div>
        @endif

        <div class="header-main d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('img/logo-sekolah-256.png') }}" alt="" width="44" height="44" class="me-2">
                <h1 class="sitename">{{ situs('nama_singkat') }}</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    @foreach ($menu as $item)
                        <li><a href="{{ $item['url'] }}" @class(['active' => $item['active']])>{{ $item['text'] }}</a></li>
                    @endforeach
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
        </div>
    </div>
</header>

<main class="main">
    @yield('content')
</main>

<footer id="footer" class="footer dark-background">
    <div class="container footer-top">
        <div class="row gy-4">
            <div class="col-lg-6 col-md-12 footer-about">
                <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                    <span class="sitename">{{ situs('nama') }}</span>
                </a>
                <p>{{ situs('deskripsi') }}</p>
                <div class="social-links d-flex mt-3">
                    @foreach (['instagram' => 'bi-instagram', 'facebook' => 'bi-facebook', 'youtube' => 'bi-youtube'] as $kunci => $ikon)
                        @if (situs($kunci))
                            <a href="{{ situs($kunci) }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($kunci) }}"><i class="bi {{ $ikon }}"></i></a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 col-md-12 footer-contact text-center text-md-start">
                <h4>Hubungi Kami</h4>
                @if (situs('alamat'))<p>{{ situs('alamat') }}</p>@endif
                @if (situs('telepon'))<p class="mt-4"><strong>Telepon:</strong> <span>{{ situs('telepon') }}</span></p>@endif
                @if (situs('email'))<p><strong>Email:</strong> <span>{{ situs('email') }}</span></p>@endif
            </div>
        </div>
    </div>

    <div class="container copyright text-center mt-4">
        <p>&copy; {{ now()->year }} <strong class="px-1 sitename">{{ situs('nama') }}</strong> Hak cipta dilindungi.</p>
    </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Kembali ke atas"><i class="bi bi-arrow-up-short"></i></a>

<div id="preloader"></div>

@stack('scripts')
</body>
</html>
