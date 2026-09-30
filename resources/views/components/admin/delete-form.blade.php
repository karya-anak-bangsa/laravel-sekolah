{{-- Tombol hapus (DELETE) dengan konfirmasi browser. --}}
@props(['action', 'message' => 'Hapus data ini? Tindakan ini tidak dapat dibatalkan.', 'label' => 'Hapus'])
<form method="POST" action="{{ $action }}" style="display:inline" onsubmit="return confirm(@js($message))">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-danger btn-sm']) }}>{{ $label }}</button>
</form>
