{{-- Formulaire RSVP : présence, coordonnées, nombre d'accompagnants, commentaire. --}}
<section id="rsvp" aria-labelledby="rsvp-title" class="bg-brand-charcoal py-20 text-white">
    <div class="mx-auto max-w-xl px-6">
        <h2 id="rsvp-title" class="text-center font-display text-3xl font-semibold sm:text-4xl">
            Confirmez votre présence
        </h2>
        <p class="mt-3 text-center font-sans text-sm text-white/70">
            Merci de répondre avant le grand jour, cela nous aide énormément à organiser la fête.
        </p>

        @if (session('rsvp_success'))
            <x-shared.confirmation-message
                class="mt-8"
                message="Merci ! Votre réponse a bien été enregistrée."
            />
        @endif

        <x-shared.form-errors class="mt-8" />

        <form method="POST" action="{{ $rsvpUrl }}" class="mt-8 space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <fieldset>
                <legend class="font-sans text-sm font-medium text-white/90">Serez-vous présent(e) ?</legend>
                <div class="mt-2 grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/20 px-4 py-3 font-sans text-sm has-[:checked]:border-brand-gold has-[:checked]:bg-brand-gold/10">
                        <input type="radio" name="status" value="present" class="accent-brand-gold" {{ old('status', 'present') === 'present' ? 'checked' : '' }} required>
                        Présent(e)
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/20 px-4 py-3 font-sans text-sm has-[:checked]:border-brand-gold has-[:checked]:bg-brand-gold/10">
                        <input type="radio" name="status" value="absent" class="accent-brand-gold" {{ old('status') === 'absent' ? 'checked' : '' }}>
                        Absent(e)
                    </label>
                </div>
            </fieldset>

            <div>
                <label for="rsvp-name" class="font-sans text-sm font-medium text-white/90">Nom complet</label>
                <input
                    type="text" id="rsvp-name" name="name" required maxlength="255" value="{{ old('name') }}"
                    class="mt-1 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 font-sans text-sm text-white placeholder-white/40 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
                    placeholder="Votre nom et prénom"
                >
            </div>

            <div>
                <label for="rsvp-phone" class="font-sans text-sm font-medium text-white/90">Téléphone (optionnel)</label>
                <input
                    type="tel" id="rsvp-phone" name="phone" maxlength="30" value="{{ old('phone') }}"
                    class="mt-1 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 font-sans text-sm text-white placeholder-white/40 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
                    placeholder="Ex. 21698123456"
                >
            </div>

            <div>
                <label for="rsvp-guests" class="font-sans text-sm font-medium text-white/90">Nombre d'accompagnants</label>
                <input
                    type="number" id="rsvp-guests" name="guests_count" min="0" max="20" value="{{ old('guests_count', 0) }}"
                    class="mt-1 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 font-sans text-sm text-white placeholder-white/40 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
                >
            </div>

            <div>
                <label for="rsvp-comment" class="font-sans text-sm font-medium text-white/90">Commentaire (optionnel)</label>
                <textarea
                    id="rsvp-comment" name="comment" rows="3" maxlength="1000"
                    class="mt-1 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 font-sans text-sm text-white placeholder-white/40 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
                    placeholder="Un message pour les mariés ?"
                >{{ old('comment') }}</textarea>
            </div>

            <button
                type="submit"
                class="w-full rounded-full bg-brand-gold px-6 py-3 font-sans text-sm font-semibold text-brand-charcoal transition hover:brightness-105"
            >
                <span x-show="!submitting">Envoyer ma réponse</span>
                <x-shared.loading-state x-show="submitting" x-cloak label="Envoi…" />
            </button>
        </form>
    </div>
</section>
