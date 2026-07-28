<section id="tarif" class="py-24 bg-brand-ivory">
    <div class="max-w-3xl mx-auto px-6">
        <div class="relative rounded-[2rem] border border-brand-gold/25 bg-white p-10 sm:p-14 text-center shadow-[0_30px_60px_-25px_rgba(43,43,43,0.15)]">
            <span class="absolute -top-4 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 rounded-full bg-brand-gold px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm">
                <x-heroicon-s-fire class="h-3.5 w-3.5" />
                En promotion
            </span>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose">Offre spéciale</p>

            <div class="mt-4 flex items-end justify-center gap-2">
                <span class="font-display text-6xl text-brand-charcoal">{{ $offerPrice }}</span>
                <span class="font-display text-2xl text-brand-charcoal/60 mb-2">{{ $offerCurrency }}</span>
            </div>
            <p class="mt-2 text-sm text-brand-charcoal/50">Paiement unique — aucun abonnement</p>

            <p class="mt-8 text-brand-charcoal/70 leading-relaxed max-w-md mx-auto">
                Nous sommes prêts à concevoir exactement l'invitation dont vous rêvez.
                Chaque mariage est unique — nous réalisons la vôtre entièrement selon vos envies.
            </p>

            <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-brand-charcoal/60">
                <li class="flex items-center gap-1.5"><x-heroicon-s-check class="h-4 w-4 text-brand-rose" /> Aucun abonnement</li>
                <li class="flex items-center gap-1.5"><x-heroicon-s-check class="h-4 w-4 text-brand-rose" /> Paiement unique</li>
                <li class="flex items-center gap-1.5"><x-heroicon-s-check class="h-4 w-4 text-brand-rose" /> Aucun coût caché</li>
            </ul>

            <x-shared.whatsapp-cta :href="$whatsappUrl" source="pricing" class="mt-10 inline-flex items-center justify-center gap-2 rounded-full bg-brand-rose text-white font-semibold px-9 py-4 text-base shadow-lg shadow-brand-rose/25 hover:bg-brand-charcoal transition-colors duration-200">
                    Commander maintenant sur WhatsApp
                </x-shared.whatsapp-cta>
        </div>
    </div>
</section>
