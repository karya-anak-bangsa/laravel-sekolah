{{--
    Layout panel admin (Gentelella v4).
    Section: title (wajib), pretitle, actions, breadcrumb (HTML), content (wajib). Stack: scripts.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-sw="off">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel Admin') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- Terapkan tema sebelum render agar tidak berkedip --}}
    <script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t||(d?'dark':'light'));}catch(e){}})();</script>
    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
    @stack('head')
</head>
<body data-shell="admin">
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <x-admin.sidebar />

    <x-admin.topbar>
        @hasSection('breadcrumb')
            @yield('breadcrumb')
        @else
            @if (Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}">Beranda</a><span class="sep" aria-hidden="true">›</span>
            @endif
            <span class="current" aria-current="page">@yield('title', 'Panel Admin')</span>
        @endif
    </x-admin.topbar>

    <main id="main-content" tabindex="-1" class="main">
        <div class="page-wrapper">
            <div class="page-header">
                <div class="page-header-row">
                    <div>
                        @hasSection('pretitle')
                            <div class="page-pretitle">@yield('pretitle')</div>
                        @endif
                        <h1 class="page-title">@yield('title', 'Panel Admin')</h1>
                    </div>
                    @hasSection('actions')
                        <div class="page-actions">@yield('actions')</div>
                    @endif
                </div>
            </div>

            @if (session('status'))
                <x-admin.alert type="success" class="flash">{{ session('status') }}</x-admin.alert>
            @endif
            @if (session('error'))
                <x-admin.alert type="error" class="flash">{{ session('error') }}</x-admin.alert>
            @endif

            @yield('content')
        </div>

        <footer class="footer">
            <span>&copy; {{ now()->year }} {{ config('sekolah.nama') }}</span>
            <span>Sistem Akademik Sekolah</span>
        </footer>
    </main>

    @stack('scripts')
</body>
</html>
