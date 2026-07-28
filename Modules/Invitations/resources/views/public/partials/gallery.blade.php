{{-- Galerie photo avec lightbox Alpine.js (core uniquement, aucun plugin/dépendance ajoutée). --}}
@if ($gallery->isNotEmpty())
    <section
        id="galerie"
        aria-labelledby="galerie-title"
        class="mx-auto max-w-5xl px-6 py-20"
        x-data="{ open: false, index: 0, images: @js($gallery->pluck('url')) }"
    >
        <h2 id="galerie-title" class="text-center font-display text-3xl font-semibold text-brand-charcoal sm:text-4xl">
            Galerie
        </h2>

        <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach ($gallery as $i => $item)
                <button
                    type="button"
                    @click="open = true; index = {{ $i }}"
                    class="group relative aspect-square overflow-hidden rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold"
                >
                    <x-shared.responsive-media
                        :src="$item['url']"
                        alt="Photo {{ $i + 1 }} du mariage de {{ $invitation->groom_name }} et {{ $invitation->bride_name }}"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                </button>
            @endforeach
        </div>

        {{-- Lightbox --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="open = false"
            @keydown.arrow-right.window="index = (index + 1) % images.length"
            @keydown.arrow-left.window="index = (index - 1 + images.length) % images.length"
            class="fixed inset-0 z-50 flex items-center justify-center bg-brand-charcoal/95 p-4"
            role="dialog"
            aria-modal="true"
            aria-label="Aperçu de la photo en plein écran"
        >
            <button
                type="button"
                @click="open = false"
                class="absolute right-4 top-4 rounded-full p-2 text-white/80 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold"
                aria-label="Fermer la galerie"
            >
                <x-heroicon-o-x-mark class="h-7 w-7" aria-hidden="true" />
            </button>

            <button
                type="button"
                @click="index = (index - 1 + images.length) % images.length"
                class="absolute left-2 rounded-full p-2 text-white/80 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold sm:left-6"
                aria-label="Photo précédente"
            >
                <x-heroicon-o-chevron-left class="h-8 w-8" aria-hidden="true" />
            </button>

            <img :src="images[index]" alt="" class="max-h-[80vh] max-w-full rounded-lg object-contain">

            <button
                type="button"
                @click="index = (index + 1) % images.length"
                class="absolute right-2 rounded-full p-2 text-white/80 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold sm:right-6"
                aria-label="Photo suivante"
            >
                <x-heroicon-o-chevron-right class="h-8 w-8" aria-hidden="true" />
            </button>
        </div>
    </section>
@endif
