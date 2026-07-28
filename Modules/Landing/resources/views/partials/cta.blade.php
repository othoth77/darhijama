<section id="contact" class="relative overflow-hidden bg-brand-charcoal py-24">
    <div class="absolute inset-0 -z-10 opacity-[0.07]" aria-hidden="true">
        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="ctaGrid" width="10" height="10" patternUnits="userSpaceOnUse">
                    <circle cx="1" cy="1" r="0.6" fill="#f1e9df" />
                </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#ctaGrid)" />
        </svg>
    </div>

    <div class="relative max-w-2xl mx-auto px-6 text-center">
        <h2 class="font-display text-3xl sm:text-4xl text-brand-ivory mb-5">
            Prêt à créer une invitation inoubliable ?
        </h2>
        <p class="text-brand-ivory/65 leading-relaxed mb-10">
            Notre équipe est prête à concevoir une invitation qui vous ressemble.
        </p>
        <x-shared.whatsapp-cta :href="$whatsappUrl" source="cta_final" class="inline-flex items-center gap-2 rounded-full bg-brand-rose text-white font-semibold px-9 py-4 shadow-lg shadow-black/20 hover:bg-brand-ivory hover:text-brand-charcoal transition-colors duration-200">
                    Commander sur WhatsApp
                </x-shared.whatsapp-cta>
    </div>
</section>
