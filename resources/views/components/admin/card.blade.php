@props(['title' => null, 'subtitle' => null, 'flush' => false])
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($options))
        <div class="card-header">
            <div>
                @if ($title)<div class="card-title">{{ $title }}</div>@endif
                @if ($subtitle)<div class="card-subtitle">{{ $subtitle }}</div>@endif
            </div>
            @isset($options)
                <div class="card-options">{{ $options }}</div>
            @endisset
        </div>
    @endif

    <div @class(['card-body', 'p-0' => $flush])>{{ $slot }}</div>

    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
