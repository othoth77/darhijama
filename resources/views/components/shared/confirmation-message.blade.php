@props([
    'type' => 'success',
    'message' => null,
])

@php
    $styles = $type === 'success'
        ? 'border-brand-gold/40 bg-brand-gold/10 text-brand-gold'
        : 'border-red-400/40 bg-red-400/10 text-red-200';
@endphp

<div role="status" {{ $attributes->class(["rounded-2xl border p-4 text-center font-sans text-sm {$styles}"]) }}>
    {{ $message ?? $slot }}
</div>
