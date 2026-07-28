{{-- Partage (WhatsApp / Facebook / copie du lien) + QR Code de l'invitation. --}}
<section id="partage" aria-labelledby="partage-title" class="mx-auto max-w-3xl px-6 py-20 text-center">
    <h2 id="partage-title" class="font-display text-3xl font-semibold text-brand-charcoal sm:text-4xl">
        Partagez l'invitation
    </h2>
    <p class="mt-3 font-sans text-sm text-brand-charcoal/70">
        Transmettez ce lien à vos proches pour qu'ils retrouvent toutes les informations du mariage.
    </p>

    <x-shared.social-sharing
        class="mt-8"
        :url="$publicUrl"
        :whatsapp-url="$whatsappShareUrl"
        whatsapp-source="invitation_share"
        :context="['invitation_id' => $invitation->id]"
    />
    @if ($facebookUrl || $instagramUrl)
        <div class="mt-6 flex justify-center gap-4">
            @if ($facebookUrl)
                <a href="{{ $facebookUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm underline">Facebook</a>
            @endif
            @if ($instagramUrl)
                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm underline">Instagram</a>
            @endif
        </div>
    @endif
    <div class="mt-10 inline-flex flex-col items-center gap-2">
        <x-shared.responsive-media
            :src="$qrUrl"
            alt="QR Code de l'invitation de {{ $invitation->groom_name }} et {{ $invitation->bride_name }}"
            width="160"
            height="160"
            class="rounded-xl border border-brand-beige shadow-sm"
        />
        <span class="font-sans text-xs text-brand-charcoal/60">Scannez pour ouvrir l'invitation</span>
    </div>
</section>
