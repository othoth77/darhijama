<header
    x-data="{ mobileOpen: false }"
    class="sticky top-0 z-50 border-b border-brand-charcoal/10 bg-brand-ivory/80 backdrop-blur-md"
>
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between h-18 py-4">
            <a href="#accueil" class="flex items-center gap-2 shrink-0">
                <span class="font-display text-2xl text-brand-charcoal tracking-tight">Notre<span class="text-brand-rose"> Jour</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-9">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="text-sm font-medium text-brand-charcoal/70 hover:text-brand-rose transition-colors duration-200">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <x-shared.whatsapp-cta :href="$whatsappUrl" source="header" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-rose text-white text-sm font-medium px-5 py-2.5 shadow-sm shadow-brand-rose/20 hover:bg-brand-charcoal transition-colors duration-200">
                    Commander sur WhatsApp
                </x-shared.whatsapp-cta>

                <button
                    @click="mobileOpen = !mobileOpen"
                    type="button"
                    aria-label="Ouvrir le menu"
                    class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-full text-brand-charcoal hover:bg-brand-charcoal/5 transition-colors"
                >
                    <x-heroicon-o-bars-3 x-show="!mobileOpen" class="h-6 w-6" />
                    <x-heroicon-o-x-mark x-show="mobileOpen" x-cloak class="h-6 w-6" />
                </button>
            </div>
        </div>

        <nav
            x-show="mobileOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="md:hidden flex flex-col gap-1 pb-5"
        >
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}" @click="mobileOpen = false" class="rounded-lg px-3 py-2.5 text-sm font-medium text-brand-charcoal/80 hover:bg-brand-charcoal/5">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <x-shared.whatsapp-cta :href="$whatsappUrl" source="header_mobile" class="mt-2 inline-flex items-center justify-center gap-2 rounded-full bg-brand-rose text-white text-sm font-medium px-5 py-2.5">
                    Commander sur WhatsApp
                </x-shared.whatsapp-cta>
        </nav>
    </div>
</header>
