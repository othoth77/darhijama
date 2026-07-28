{{-- Programme du mariage : étapes ordonnées (heure / titre / description). --}}
@if ($programSteps->isNotEmpty())
    <section id="programme" aria-labelledby="programme-title" class="bg-brand-beige/40 py-20">
        <div class="mx-auto max-w-3xl px-6">
            <h2 id="programme-title" class="text-center font-display text-3xl font-semibold text-brand-charcoal sm:text-4xl">
                Programme
            </h2>

            <ol class="mt-12 space-y-8 border-l border-brand-gold/40 pl-8">
                @foreach ($programSteps as $step)
                    <li class="relative">
                        <span class="absolute -left-[2.35rem] top-1 flex h-4 w-4 items-center justify-center rounded-full bg-brand-gold" aria-hidden="true"></span>

                        <div class="flex flex-wrap items-baseline gap-x-3">
                            @if ($step->time)
                                <span class="font-display text-sm font-semibold text-brand-gold">
                                    {{ \Illuminate\Support\Carbon::parse($step->time)->format('H:i') }}
                                </span>
                            @endif
                            <h3 class="font-display text-lg font-semibold text-brand-charcoal">{{ $step->title }}</h3>
                        </div>

                        @if ($step->description)
                            <p class="mt-1 font-sans text-sm text-brand-charcoal/70">{{ $step->description }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif
