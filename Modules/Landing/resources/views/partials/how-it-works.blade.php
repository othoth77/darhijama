<section id="comment-ca-marche" class="py-24 bg-brand-beige/50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="max-w-2xl mx-auto text-center mb-16">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose mb-3">Simple et rapide</p>
            <h2 class="font-display text-3xl sm:text-4xl text-brand-charcoal">Comment ça marche</h2>
        </div>

        <div class="relative grid sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-14">
            {{-- Ligne de connexion, visible uniquement en desktop --}}
            <div class="hidden lg:block absolute top-6 left-[12.5%] right-[12.5%] h-px bg-brand-charcoal/10" aria-hidden="true"></div>

            @foreach ($steps as $step)
                <div class="relative text-center lg:text-left">
                    <div class="mx-auto lg:mx-0 relative z-10 flex h-12 w-12 items-center justify-center rounded-full bg-brand-charcoal text-brand-ivory font-display text-base mb-5">
                        {{ $step['number'] }}
                    </div>
                    <h3 class="font-semibold text-brand-charcoal mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-brand-charcoal/60 leading-relaxed">{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
