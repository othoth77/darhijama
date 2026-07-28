@props([
    'href',
    'source',
    'context' => [],
])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener noreferrer"
    x-data="whatsappCta(@js($source), @js($context))"
    @click="track()"
    {{ $attributes }}
>
    {{ $slot }}
</a>
