@props(['name', 'size' => 18])
@php
    // Ikon garis dari Gentelella (ICONS di node_modules/gentelella/src/v4/shell-render.js).
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/>',
        'forms' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 13h4"/>',
        'tables' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M9 10v9M15 10v9"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M8 4v6M16 4v6"/>',
        'pages' => '<rect x="2" y="3" width="20" height="18" rx="2"/><path d="M2 8h20"/>',
        'users' => '<circle cx="12" cy="8" r="4"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/>',
        'profile' => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/>',
        'layout' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 9v12"/>',
        // Ikon tambahan milik kita (gaya garis yang sama): mata terbuka/tercoret.
        'eye' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M17.9 17.9A10.1 10.1 0 0112 19C5.6 19 2 12 2 12a18.5 18.5 0 015.1-5.9M9.9 5.2A9.9 9.9 0 0112 5c6.4 0 10 7 10 7a18.6 18.6 0 01-2.2 3.2M1 1l22 22M14.1 14.1a3 3 0 11-4.2-4.2"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
