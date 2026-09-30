{{--
    Dasar halaman error. Sengaja mandiri (CSS inline, tanpa Vite/DB/sesi) agar tetap tampil saat
    aplikasi bermasalah. Section: kode, judul, pesan.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('kode') — @yield('judul')</title>
    <style>
        :root { --bg: #f5f7fb; --card: #fff; --text: #1e2633; --muted: #626d7d; --accent: #1abb9c; --border: #e6e7eb; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #11161f; --card: #1a2332; --text: #e6eaf0; --muted: #9aa7b8; --border: #2a3547; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;
               background: var(--bg); color: var(--text); font: 15px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .box { width: 100%; max-width: 440px; text-align: center; background: var(--card); border: 1px solid var(--border);
               border-radius: 10px; padding: 40px 28px; }
        .kode { font-size: 64px; font-weight: 700; line-height: 1; color: var(--accent); margin: 0 0 8px; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { color: var(--muted); margin: 0 0 24px; }
        a.tombol { display: inline-block; padding: 9px 18px; border-radius: 6px; background: var(--accent); color: #fff;
                   text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <main class="box">
        <div class="kode">@yield('kode')</div>
        <h1>@yield('judul')</h1>
        <p>@yield('pesan')</p>
        <a class="tombol" href="{{ url('/') }}">Kembali ke beranda</a>
    </main>
</body>
</html>
