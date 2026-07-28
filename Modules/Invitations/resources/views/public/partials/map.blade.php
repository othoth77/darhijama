{{-- Carte Google Maps élégante + bouton d'ouverture externe. --}}
@if ($invitation->maps_embed_url || ($invitation->lat && $invitation->lng))
    <section id="acces" aria-labelledby="acces-title" class="mx-auto max-w-4xl px-6 pb-20">
        <h2 id="acces-title" class="sr-only">Accès</h2>

        <div class="overflow-hidden rounded-2xl border border-brand-beige shadow-sm">
            @if ($invitation->maps_embed_url)
                <iframe
                    src="{{ $invitation->maps_embed_url }}"
                    class="h-80 w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Carte du lieu du mariage"
                ></iframe>
            @endif

            <div class="flex justify-center bg-white p-4">
                @php
                    $mapsHref = $invitation->maps_embed_url
                        ?: "https://www.google.com/maps/search/?api=1&query={$invitation->lat},{$invitation->lng}";
                @endphp
                <a
                    href="{{ $mapsHref }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-full border border-brand-charcoal/20 px-5 py-2.5 font-sans text-sm font-medium text-brand-charcoal transition hover:border-brand-gold hover:text-brand-gold"
                >
                    <x-heroicon-o-map class="h-4 w-4" aria-hidden="true" />
                    Ouvrir dans Google Maps
                </a>
            </div>
        </div>
    </section>
@endif
