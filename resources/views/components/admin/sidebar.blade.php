{{-- Markup mengikuti renderSidebar() Gentelella v4 agar perilaku JS-nya (accordion, rail, drawer) tetap jalan. --}}
<aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand">
        <div class="brand-icon">{{ mb_substr(config('sekolah.nama_singkat'), 0, 1) }}</div>
        <div class="brand-name">{{ config('sekolah.nama_singkat') }}</div>
    </div>

    <nav class="sidebar-nav">
        @foreach ($groups as $group)
            <div class="nav-group">
                <div class="nav-label">{{ $group['label'] }}</div>
                @foreach ($group['items'] as $item)
                    <a class="nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>
                        <x-admin.icon :name="$item['icon']" />
                        <span class="nav-text">{{ $item['text'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    @if ($user)
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="avatar">{{ mb_strtoupper(mb_substr($user->nama, 0, 1)) }}<span class="online"></span></div>
                <div class="sidebar-user-info">
                    <div class="name">{{ $user->nama }}</div>
                    <div class="role">{{ $user->getRoleNames()->first() ? str($user->getRoleNames()->first())->replace('_', ' ')->title() : 'Pengguna' }}</div>
                </div>
            </div>
        </div>
    @endif
</aside>
