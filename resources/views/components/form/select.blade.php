{{-- $options: array nilai => label. Bootstrap memakai .form-select, Gentelella memakai .form-control. --}}
@props(['name', 'options' => [], 'label' => null, 'value' => null, 'required' => false, 'help' => null, 'placeholder' => '— Pilih —', 'theme' => 'admin'])
@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $invalid = $errors->has(str_replace(['[', ']'], ['.', ''], $name));
    $selected = (string) old(str_replace(['[', ']'], ['.', ''], $name), $value);
@endphp
<x-form.field :name="$name" :label="$label" :required="$required" :help="$help" :theme="$theme" :id="$id">
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @required($required)
        {{ $attributes->except('id')->class([$theme === 'admin' ? 'form-control' : 'form-select', 'is-invalid' => $invalid]) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === $selected)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-form.field>
