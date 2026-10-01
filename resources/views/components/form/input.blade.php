@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'theme' => 'admin'])
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
    // Password tidak pernah diisi ulang setelah validasi gagal.
    // Tombol lihat password hanya untuk tema admin (situs publik memakai Bootstrap).
    $toggle = $type === 'password' && $theme === 'admin';
    $current = $type === 'password' ? null : old(str_replace(['[', ']'], ['.', ''], $name), $value);
@endphp
<x-form.field :name="$name" :label="$label" :required="$required" :help="$help" :theme="$theme" :id="$id">
    @if ($toggle)
        <div class="input-password">
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($current !== null) value="{{ $current }}" @endif
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $invalid]) }}
    >
    @if ($toggle)
            {{-- Perilakunya di resources/js/admin.js (delegasi klik pada [data-toggle-password]). --}}
            <button type="button" class="input-password-toggle" data-toggle-password="#{{ $id }}" aria-label="Tampilkan password" aria-pressed="false">
                <x-admin.icon name="eye" :size="18" data-icon="show" />
                <x-admin.icon name="eye-off" :size="18" data-icon="hide" hidden />
            </button>
        </div>
    @endif
</x-form.field>
