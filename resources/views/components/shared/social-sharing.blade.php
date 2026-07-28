@props([
    'url',
    'whatsappUrl',
    'whatsappSource' => 'social_share',
    'context' => [],
])

<div {{ $attributes->class(['flex flex-wrap items-center justify-center gap-3']) }}>
    <x-shared.whatsapp-cta
        :href="$whatsappUrl"
        :source="$whatsappSource"
        :context="$context"
        class="inline-flex items-center gap-2 rounded-full bg-brand-charcoal px-5 py-2.5 font-sans text-sm font-medium text-white transition hover:brightness-110"
    >
        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" />
        WhatsApp
    </x-shared.whatsapp-cta>

    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-2 rounded-full border border-brand-charcoal/20 px-5 py-2.5 font-sans text-sm font-medium text-brand-charcoal transition hover:border-brand-gold hover:text-brand-gold"
    >
        <x-heroicon-o-share class="h-4 w-4" aria-hidden="true" />
        Facebook
    </a>

    <button
        type="button"
        x-data="{ copied: false }"
        @click="navigator.clipboard.writeText(@js($url)); copied = true; setTimeout(() => copied = false, 2000)"
        class="inline-flex items-center gap-2 rounded-full border border-brand-charcoal/20 px-5 py-2.5 font-sans text-sm font-medium text-brand-charcoal transition hover:border-brand-gold hover:text-brand-gold"
    >
        <x-heroicon-o-link class="h-4 w-4" aria-hidden="true" />
        <span x-show="!copied">Copier le lien</span>
        <span x-show="copied" x-cloak>Lien copié !</span>
    </button>
</div>
