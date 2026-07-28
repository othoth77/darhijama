@props([
    'type' => 'image',
    'src',
    'alt' => '',
    'loading' => 'lazy',
    'width' => null,
    'height' => null,
])

@if ($type === 'video')
    <video src="{{ $src }}" controls preload="none" playsinline {{ $attributes }}>{{ $slot }}</video>
@elseif ($type === 'audio')
    <audio src="{{ $src }}" controls preload="none" {{ $attributes }}>{{ $slot }}</audio>
@else
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $loading }}"
        @if ($width) width="{{ $width }}" @endif
        @if ($height) height="{{ $height }}" @endif
        {{ $attributes }}
    >
@endif
