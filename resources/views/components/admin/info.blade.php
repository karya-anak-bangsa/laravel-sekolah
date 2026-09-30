{{-- Pasangan label-nilai untuk halaman detail. Nilai kosong tampil sebagai tanda strip. --}}
@props(['label'])
<div class="info">
    <div class="info-label">{{ $label }}</div>
    <div class="info-value">{{ trim((string) $slot) !== '' ? $slot : '—' }}</div>
</div>
