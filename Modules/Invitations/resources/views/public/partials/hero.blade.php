{{-- Section d'ouverture : noms, message de bienvenue, compte à rebours. --}}
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-full focus:bg-brand-charcoal focus:px-5 focus:py-2 focus:text-sm focus:font-medium focus:text-white">
    Aller au contenu
</a>

<header
    class="relative overflow-hidden bg-brand-charcoal text-white"
    @if ($heroUrl) style="background-image: linear-gradient(180deg, rgba(43,43,43,.55), rgba(43,43,43,.85)), url('{{ $heroUrl }}'); background-size: cover; background-position: center;" @endif
>
    <div class="absolute inset-0 bg-linear-to-b from-brand-charcoal/10 via-brand-charcoal/40 to-brand-charcoal" aria-hidden="true"></div>

    <div class="relative mx-auto flex min-h-[85vh] max-w-3xl flex-col items-center justify-center px-6 py-24 text-center">
        <p class="font-sans text-xs font-semibold uppercase tracking-[0.3em] text-brand-gold">
            {{ $invitation->event_type === 'mariage' ? 'Nous nous marions' : ucfirst($invitation->event_type) }}
        </p>

        <h1 class="mt-6 font-display text-4xl font-semibold leading-tight sm:text-6xl">
            {{ $invitation->groom_name }} <span class="text-brand-gold">&amp;</span> {{ $invitation->bride_name }}
        </h1>

        <p class="mt-4 font-sans text-lg text-white/80">
            {{ $invitation->wedding_date->locale('fr')->isoFormat('dddd D MMMM YYYY') }} — {{ $invitation->wedding_date->format('H:i') }}
        </p>

        @if ($invitation->message)
            <p class="mt-8 max-w-xl font-sans text-base leading-relaxed text-white/90">
                {{ $invitation->message }}
            </p>
        @endif

        {{-- Compte à rebours : réutilise le composant Alpine "countdown" existant (resources/js/app.js), non modifié. --}}
        <div
            x-data="countdown('{{ $invitation->wedding_date->toIso8601String() }}')"
            class="mt-10 grid grid-cols-5 gap-2 sm:gap-4"
            role="timer"
            aria-live="off"
            aria-label="Compte à rebours avant le mariage"
        >
            <div class="flex flex-col items-center rounded-2xl bg-white/10 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-4">
                <span class="font-display text-xl font-semibold sm:text-3xl" x-text="Math.floor(days / 365)">0</span>
                <span class="mt-1 text-[10px] uppercase tracking-wide text-white/70 sm:text-xs">Années</span>
            </div>
            <div class="flex flex-col items-center rounded-2xl bg-white/10 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-4">
                <span class="font-display text-xl font-semibold sm:text-3xl" x-text="days % 365">0</span>
                <span class="mt-1 text-[10px] uppercase tracking-wide text-white/70 sm:text-xs">Jours</span>
            </div>
            <div class="flex flex-col items-center rounded-2xl bg-white/10 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-4">
                <span class="font-display text-xl font-semibold sm:text-3xl" x-text="hours">0</span>
                <span class="mt-1 text-[10px] uppercase tracking-wide text-white/70 sm:text-xs">Heures</span>
            </div>
            <div class="flex flex-col items-center rounded-2xl bg-white/10 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-4">
                <span class="font-display text-xl font-semibold sm:text-3xl" x-text="minutes">0</span>
                <span class="mt-1 text-[10px] uppercase tracking-wide text-white/70 sm:text-xs">Minutes</span>
            </div>
            <div class="flex flex-col items-center rounded-2xl bg-white/10 px-2 py-3 backdrop-blur-sm sm:px-4 sm:py-4">
                <span class="font-display text-xl font-semibold sm:text-3xl" x-text="seconds">0</span>
                <span class="mt-1 text-[10px] uppercase tracking-wide text-white/70 sm:text-xs">Secondes</span>
            </div>
        </div>

        <a
            href="#rsvp"
            class="mt-10 inline-flex items-center justify-center rounded-full bg-brand-gold px-8 py-3 font-sans text-sm font-semibold text-brand-charcoal shadow-lg transition hover:brightness-105"
        >
            Confirmer ma présence
        </a>
    </div>
</header>
