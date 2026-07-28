<section id="avantages" class="py-24">
    <div class="max-w-6xl mx-auto px-6">
        <div class="max-w-2xl mx-auto text-center mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose mb-3">L'essentiel, sans compromis</p>
            <h2 class="font-display text-3xl sm:text-4xl text-brand-charcoal">Tout ce qu'il faut pour une invitation mémorable</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-x-8 gap-y-12">
            @foreach ($features as $feature)
                <div class="group">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-rose/10 text-brand-rose mb-4 transition-colors duration-300 group-hover:bg-brand-rose group-hover:text-white">
                        <x-dynamic-component :component="'heroicon-o-' . $feature['icon']" class="h-6 w-6" />
                    </div>
                    <h3 class="font-semibold text-brand-charcoal mb-1.5">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-brand-charcoal/60 leading-relaxed">{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
