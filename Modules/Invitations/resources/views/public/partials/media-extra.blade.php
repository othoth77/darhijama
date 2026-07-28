{{-- Vidéo optionnelle + lecteur musique (lecture/pause manuelle, aucune lecture automatique). --}}
@if ($videoUrl)
    <section id="video" aria-labelledby="video-title" class="mx-auto max-w-3xl px-6 pb-20">
        <h2 id="video-title" class="text-center font-display text-3xl font-semibold text-brand-charcoal sm:text-4xl">
            Vidéo
        </h2>
        <div class="mt-8 overflow-hidden rounded-2xl border border-brand-beige shadow-sm">
            <x-shared.responsive-media type="video" :src="$videoUrl" class="w-full">
                Votre navigateur ne prend pas en charge la lecture vidéo.
            </x-shared.responsive-media>
        </div>
    </section>
@endif

@if (! $videoUrl && $externalVideoUrl)
    <div class="mx-auto max-w-3xl px-6 pb-10 text-center">
        <a href="{{ $externalVideoUrl }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex rounded-full border border-brand-charcoal/20 px-5 py-2.5 text-sm font-medium">
            Voir la vidéo
        </a>
    </div>
@endif

@if ($musicUrl)
    <div
        x-data="{ playing: false }"
        class="fixed bottom-5 left-5 z-40"
    >
        <audio x-ref="audio" src="{{ $musicUrl }}" preload="none" loop
            @play="playing = true" @pause="playing = false" @ended="playing = false"></audio>

        <button
            type="button"
            @click="playing ? $refs.audio.pause() : $refs.audio.play()"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-charcoal text-white shadow-lg transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold"
            :aria-label="playing ? 'Mettre la musique en pause' : 'Lire la musique'"
        >
            <x-heroicon-o-pause class="h-6 w-6" x-show="playing" x-cloak aria-hidden="true" />
            <x-heroicon-o-play class="h-6 w-6" x-show="!playing" aria-hidden="true" />
        </button>
    </div>
@endif

@if (! $musicUrl && $externalAudioUrl)
    <div class="mx-auto max-w-3xl px-6 pb-10 text-center">
        <a href="{{ $externalAudioUrl }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex rounded-full border border-brand-charcoal/20 px-5 py-2.5 text-sm font-medium">
            Écouter l’audio
        </a>
    </div>
@endif
