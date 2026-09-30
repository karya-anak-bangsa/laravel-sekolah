@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'theme' => 'admin'])
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
    // Password tidak pernah diisi ulang setelah validasi gagal.
    $current = $type === 'password' ? null : old(str_replace(['[', ']'], ['.', ''], $name), $value);
@endphp
<x-form.field :name="$name" :label="$label" :required="$required" :help="$help" :theme="$theme" :id="$id">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($current !== null) value="{{ $current }}" @endif
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $invalid]) }}
    >
</x-form.field>
