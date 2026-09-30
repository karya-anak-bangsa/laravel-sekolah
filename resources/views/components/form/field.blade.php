{{--
    Pembungkus field: label, kontrol (slot), teks bantuan, dan pesan error di bawah field.
    theme "admin" memakai kelas Gentelella; theme "public" memakai Bootstrap 5 (situs UniPulse).
--}}
@props(['name', 'label' => null, 'required' => false, 'help' => null, 'theme' => 'admin', 'id' => null])
@php
    $id ??= str_replace(['[', ']', '.'], ['_', '', '_'], $name);
    $error = $errors->first(str_replace(['[', ']'], ['.', ''], $name));
    $admin = $theme === 'admin';
@endphp
<div @class([$admin ? 'form-group' : 'mb-3'])>
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required)<span class="{{ $admin ? 'required' : 'text-danger' }}"> *</span>@endif</label>
    @endif

    {{ $slot }}

    @if ($error)
        <div @class([$admin ? 'form-error' : 'invalid-feedback d-block'])>{{ $error }}</div>
    @elseif ($help)
        <div @class([$admin ? 'form-help' : 'form-text'])>{{ $help }}</div>
    @endif
</div>
