@props(['type' => 'info'])
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'role' => $type === 'error' ? 'alert' : 'status']) }}>
    <div class="alert-body">{{ $slot }}</div>
</div>
