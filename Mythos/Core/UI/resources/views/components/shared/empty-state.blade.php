@props([
    'title',
    'description' => null,
    'icon' => 'heroicon-o-inbox',
])

<div role="status" {{ $attributes->class(['rounded-2xl border border-dashed border-brand-beige p-8 text-center']) }}>
    <x-dynamic-component :component="$icon" class="mx-auto h-8 w-8 text-brand-charcoal/40" aria-hidden="true" />
    <p class="mt-3 font-sans font-medium text-brand-charcoal">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 font-sans text-sm text-brand-charcoal/60">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
