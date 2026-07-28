<section id="accueil" class="relative overflow-hidden">
    {{-- Aplat de fond très doux, purement décoratif --}}
    <div class="absolute inset-0 bg-linear-to-b from-brand-beige/60 via-brand-ivory to-brand-ivory -z-10" aria-hidden="true"></div>

    <div class="max-w-6xl mx-auto px-6 pt-20 pb-24 grid lg:grid-cols-2 gap-16 items-center">
        <div
            x-data="{ shown: false }"
            x-init="setTimeout(() => shown = true, 80)"
            :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'"
            class="transition-all duration-700 ease-out"
        >
            <p class="inline-flex items-center gap-2 rounded-full border border-brand-gold/30 bg-brand-gold/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-gold mb-7">
                <x-heroicon-s-sparkles class="h-3.5 w-3.5" />
                Invitations de mariage digitales
            </p>

            <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] leading-[1.12] text-brand-charcoal mb-7">
                Votre mariage mérite une invitation aussi unique que votre histoire.
            </h1>

            <p class="text-lg text-brand-charcoal/65 leading-relaxed max-w-lg mb-10">
                Créez une invitation digitale élégante, entièrement personnalisée et
                partageable instantanément avec tous vos invités.
            </p>

            <div class="flex flex-wrap items-center gap-4">
                <x-shared.whatsapp-cta :href="$whatsappUrl" source="hero" class="inline-flex items-center gap-2 rounded-full bg-brand-rose text-white font-medium px-7 py-3.5 shadow-md shadow-brand-rose/25 hover:bg-brand-charcoal transition-colors duration-200">
                    Commander sur WhatsApp
                </x-shared.whatsapp-cta>
                <a href="#modeles"
                   class="inline-flex items-center gap-2 rounded-full border border-brand-charcoal/15 text-brand-charcoal font-medium px-7 py-3.5 hover:border-brand-charcoal/40 transition-colors duration-200">
                    Voir des exemples
                </a>
            </div>

            <div class="mt-12 flex items-center gap-8 text-sm text-brand-charcoal/50">
                <div>
                    <p class="font-display text-2xl text-brand-charcoal">49&nbsp;DT</p>
                    <p>Paiement unique</p>
                </div>
                <div class="h-8 w-px bg-brand-charcoal/10"></div>
                <div>
                    <p class="font-display text-2xl text-brand-charcoal">100%</p>
                    <p>Sur mesure</p>
                </div>
            </div>
        </div>

        {{-- Illustration premium : composition invitation digitale sur smartphone --}}
        <div class="relative flex justify-center lg:justify-end" aria-hidden="true">
            <div class="absolute -z-10 h-[420px] w-[420px] rounded-full bg-brand-rose/10 blur-3xl"></div>

            <svg viewBox="0 0 480 600" class="w-full max-w-sm drop-shadow-xl" role="img" aria-label="Illustration d'une invitation de mariage digitale affichée sur un smartphone">
                <defs>
                    <linearGradient id="phoneScreen" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#faf6f1" />
                        <stop offset="100%" stop-color="#f1e9df" />
                    </linearGradient>
                </defs>

                <ellipse cx="240" cy="560" rx="150" ry="18" fill="#2b2b2b" opacity="0.06" />

                <circle cx="70" cy="120" r="5" fill="#c9a24b" opacity="0.55" />
                <circle cx="410" cy="90" r="4" fill="#b76e79" opacity="0.5" />
                <circle cx="420" cy="480" r="6" fill="#c9a24b" opacity="0.4" />
                <path d="M40 380 q20 -16 40 0" stroke="#b76e79" stroke-width="2" fill="none" opacity="0.4" stroke-linecap="round" />
                <path d="M405 200 q20 -16 40 0" stroke="#c9a24b" stroke-width="2" fill="none" opacity="0.45" stroke-linecap="round" />

                <rect x="105" y="30" width="270" height="530" rx="42" fill="#2b2b2b" />
                <rect x="115" y="42" width="250" height="506" rx="32" fill="url(#phoneScreen)" />

                <rect x="150" y="90" width="180" height="1" fill="#b76e79" opacity="0.3" />

                <g transform="translate(240 150)">
                    <circle r="34" fill="none" stroke="#c9a24b" stroke-width="1.4" opacity="0.65" />
                    <path d="M-14 4c0 -12 10 -18 14 -8c4 -10 14 -4 14 8c0 10 -14 20 -14 20s-14 -10 -14 -20z" fill="#b76e79" opacity="0.85" />
                </g>

                <text x="240" y="240" text-anchor="middle" font-family="Playfair Display, serif" font-size="26" fill="#2b2b2b">Ahmed &amp; Salma</text>
                <text x="240" y="266" text-anchor="middle" font-family="Inter, sans-serif" font-size="11" letter-spacing="3" fill="#b76e79">VOUS INVITENT</text>

                <rect x="150" y="296" width="180" height="1" fill="#2b2b2b" opacity="0.12" />

                <text x="240" y="330" text-anchor="middle" font-family="Inter, sans-serif" font-size="13" fill="#2b2b2b" opacity="0.75">12 Septembre 2026</text>
                <text x="240" y="352" text-anchor="middle" font-family="Inter, sans-serif" font-size="13" fill="#2b2b2b" opacity="0.75">Tunis, Tunisie</text>

                <rect x="180" y="392" width="120" height="38" rx="19" fill="#b76e79" />
                <text x="240" y="416" text-anchor="middle" font-family="Inter, sans-serif" font-size="12" font-weight="600" fill="#faf6f1">Voir l'invitation</text>

                <g transform="translate(150 452)" opacity="0.9">
                    <rect width="180" height="52" rx="14" fill="#faf6f1" stroke="#2b2b2b" stroke-opacity="0.08" />
                    <circle cx="26" cy="26" r="13" fill="#25D366" />
                    <path d="M20 26c0 -4 3 -6 6 -6s6 2 6 6s-3 7 -6 7c-1.3 0 -2.4 -0.3 -3.3 -0.8l-3.7 1l1 -3.5c-0.7 -1.1 -1 -2.4 -1 -3.7z" fill="#fff" />
                    <text x="50" y="23" font-family="Inter, sans-serif" font-size="10.5" font-weight="600" fill="#2b2b2b">Partagé sur WhatsApp</text>
                    <text x="50" y="37" font-family="Inter, sans-serif" font-size="9.5" fill="#2b2b2b" opacity="0.5">Envoyé à 120 invités</text>
                </g>
            </svg>
        </div>
    </div>
</section>
