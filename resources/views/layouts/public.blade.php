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
    <title>@yield('title', config('sekolah.nama')) | {{ config('sekolah.nama_singkat') }}</title>
    <meta name="description" content="@yield('description', config('sekolah.deskripsi'))">
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
        @if (config('sekolah.email') || config('sekolah.telepon'))
            <div class="header-top d-flex align-items-center justify-content-between">
                <div class="contact-info d-none d-lg-flex align-items-center">
                    @if (config('sekolah.email'))
                        <i class="bi bi-envelope"></i>
                        <a href="mailto:{{ config('sekolah.email') }}">{{ config('sekolah.email') }}</a>
                    @endif
                    @if (config('sekolah.telepon'))
                        <i class="bi bi-phone ms-4"></i>
                        <span>{{ config('sekolah.telepon') }}</span>
                    @endif
                </div>
            </div>
        @endif

        <div class="header-main d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <h1 class="sitename">{{ config('sekolah.nama_singkat') }}</h1>
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
                    <span class="sitename">{{ config('sekolah.nama') }}</span>
                </a>
                <p>{{ config('sekolah.deskripsi') }}</p>
            </div>

            <div class="col-lg-6 col-md-12 footer-contact text-center text-md-start">
                <h4>Hubungi Kami</h4>
                @if (config('sekolah.alamat'))<p>{{ config('sekolah.alamat') }}</p>@endif
                @if (config('sekolah.telepon'))<p class="mt-4"><strong>Telepon:</strong> <span>{{ config('sekolah.telepon') }}</span></p>@endif
                @if (config('sekolah.email'))<p><strong>Email:</strong> <span>{{ config('sekolah.email') }}</span></p>@endif
            </div>
        </div>
    </div>

    <div class="container copyright text-center mt-4">
        <p>&copy; {{ now()->year }} <strong class="px-1 sitename">{{ config('sekolah.nama') }}</strong> Hak cipta dilindungi.</p>
    </div>
</footer>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Kembali ke atas"><i class="bi bi-arrow-up-short"></i></a>

<div id="preloader"></div>

@stack('scripts')
</body>
</html>
