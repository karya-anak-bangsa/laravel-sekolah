@props(['name', 'label' => null, 'value' => null, 'required' => false, 'help' => null, 'theme' => 'admin'])
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
@endphp
<x-form.field :name="$name" :label="$label" :required="$required" :help="$help" :theme="$theme" :id="$id">
    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        @required($required)
        {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $invalid]) }}
    >{{ old(str_replace(['[', ']'], ['.', ''], $name), $value) }}</textarea>
</x-form.field>
