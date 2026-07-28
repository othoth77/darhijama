{{-- Informations pratiques : date/heure, lieu, dress code, contact, infos complémentaires. --}}
<section id="informations" aria-labelledby="informations-title" class="mx-auto max-w-4xl px-6 py-20">
    <h2 id="informations-title" class="text-center font-display text-3xl font-semibold text-brand-charcoal sm:text-4xl">
        Informations pratiques
    </h2>

    <div class="mt-12 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-brand-beige bg-white p-6 shadow-sm">
            <x-heroicon-o-calendar-days class="h-6 w-6 text-brand-gold" aria-hidden="true" />
            <h3 class="mt-3 font-display text-lg font-semibold">Date &amp; heure</h3>
            <p class="mt-1 font-sans text-sm text-brand-charcoal/70">
                {{ $invitation->wedding_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}<br>
                à {{ $invitation->wedding_date->format('H:i') }}
            </p>
        </div>

        @if ($invitation->venue_name || $invitation->venue_address)
            <div class="rounded-2xl border border-brand-beige bg-white p-6 shadow-sm">
                <x-heroicon-o-map-pin class="h-6 w-6 text-brand-gold" aria-hidden="true" />
                <h3 class="mt-3 font-display text-lg font-semibold">Lieu</h3>
                <p class="mt-1 font-sans text-sm text-brand-charcoal/70">
                    @if ($invitation->venue_name) {{ $invitation->venue_name }}<br> @endif
                    {{ $invitation->venue_address }}
                </p>
            </div>
        @endif

        @if ($invitation->dress_code)
            <div class="rounded-2xl border border-brand-beige bg-white p-6 shadow-sm">
                <x-heroicon-o-sparkles class="h-6 w-6 text-brand-gold" aria-hidden="true" />
                <h3 class="mt-3 font-display text-lg font-semibold">Dress code</h3>
                <p class="mt-1 font-sans text-sm text-brand-charcoal/70">{{ $invitation->dress_code }}</p>
            </div>
        @endif

        @if ($invitation->contact_name || $invitation->contact_phone)
            <div class="rounded-2xl border border-brand-beige bg-white p-6 shadow-sm">
                <x-heroicon-o-phone class="h-6 w-6 text-brand-gold" aria-hidden="true" />
                <h3 class="mt-3 font-display text-lg font-semibold">Contact</h3>
                <p class="mt-1 font-sans text-sm text-brand-charcoal/70">
                    @if ($invitation->contact_name) {{ $invitation->contact_name }}<br> @endif
                    {{ $invitation->contact_phone }}
                </p>
            </div>
        @endif
    </div>

    @if ($invitation->additional_info)
        <div class="mt-6 rounded-2xl border border-brand-beige bg-brand-beige/40 p-6">
            <h3 class="font-display text-lg font-semibold">Informations complémentaires</h3>
            <p class="mt-1 whitespace-pre-line font-sans text-sm text-brand-charcoal/70">{{ $invitation->additional_info }}</p>
        </div>
    @endif
</section>
