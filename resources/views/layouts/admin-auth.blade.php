{{--
    Layout halaman masuk panel admin (kartu di tengah, tanpa sidebar).
    Section: title (wajib), content (wajib).
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-sw="off">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Masuk') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t||(d?'dark':'light'));}catch(e){}})();</script>
    @vite(['resources/scss/admin.scss', 'resources/js/admin.js'])
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-icon">{{ mb_substr(config('sekolah.nama_singkat'), 0, 1) }}</div>
                <div class="brand-name">{{ config('sekolah.nama_singkat') }}</div>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>
