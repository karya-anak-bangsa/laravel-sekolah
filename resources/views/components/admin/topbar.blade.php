@php($user = auth()->user())
<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" type="button" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            @if (trim($slot))
                {{ $slot }}
            @else
                <span class="current" aria-current="page">Beranda</span>
            @endif
        </nav>
    </div>

    <div class="topbar-right">
        <button class="tb-btn theme-toggle" type="button" title="Ganti tema" aria-label="Ganti tema" aria-pressed="false">
            <svg class="theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            <svg class="theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>

        @if ($user)
            <details class="acct">
                <summary aria-label="Menu akun"><span class="acct-avatar">{{ mb_strtoupper(mb_substr($user->nama, 0, 1)) }}</span></summary>
                <div class="acct-menu">
                    <div class="acct-name">{{ $user->nama }}</div>
                    <div class="acct-role">{{ $user->username }}</div>
                    <hr>
                    @if (Route::has('admin.password.edit'))
                        <a href="{{ route('admin.password.edit') }}">Ganti kata sandi</a>
                    @endif
                    @if (Route::has('admin.logout'))
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit">Keluar</button>
                        </form>
                    @endif
                </div>
            </details>
        @endif
    </div>
</header>
