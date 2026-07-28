<section id="modeles" class="py-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex flex-wrap items-end justify-between gap-6 mb-14">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose mb-3">Une sélection soignée</p>
                <h2 class="font-display text-3xl sm:text-4xl text-brand-charcoal">Nos modèles</h2>
            </div>
            <a href="{{ route('templates.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-charcoal hover:text-brand-rose transition-colors duration-200">
                Voir tous les modèles
                <x-heroicon-o-arrow-right class="h-4 w-4" />
            </a>
        </div>

        @if ($gallery->isEmpty())
            <x-shared.empty-state class="mt-8" title="Les modèles arrivent bientôt" description="Contactez-nous pour découvrir les créations disponibles." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($gallery as $item)
                    <article class="group">
                        <a href="{{ $item->detailUrl }}" class="block" aria-label="Voir le modèle {{ $item->name }}">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-2xl border border-brand-charcoal/5 bg-linear-to-br from-brand-rose/20 via-brand-beige to-brand-ivory transition-transform duration-300 group-hover:-translate-y-1.5">
                                @if ($item->previewUrl)
                                    <x-shared.responsive-media
                                        :src="$item->previewUrl"
                                        alt="Aperçu du modèle {{ $item->name }}"
                                        width="480"
                                        height="640"
                                        class="h-full w-full object-cover"
                                    />
                                @else
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 p-6 text-center">
                                        <span class="font-display text-lg text-brand-charcoal">{{ $item->name }}</span>
                                        <span class="h-px w-8 bg-brand-gold/60"></span>
                                        <span class="text-[11px] uppercase tracking-widest text-brand-charcoal/50">{{ $item->category }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <h3 class="font-display text-xl">{{ $item->name }}</h3>
                                <p class="mt-1 text-xs uppercase tracking-widest text-brand-charcoal/50">{{ $item->category }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
