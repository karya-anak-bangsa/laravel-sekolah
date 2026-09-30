{{-- Penanda status berwarna: type green | yellow | blue | red --}}
@props(['type' => 'green'])
<span {{ $attributes->merge(['class' => 'status status-'.$type]) }}>{{ $slot }}</span>
