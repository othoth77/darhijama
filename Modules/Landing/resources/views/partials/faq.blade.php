<section id="faq" class="py-24 bg-brand-beige/50">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-14">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose mb-3">Questions fréquentes</p>
            <h2 class="font-display text-3xl sm:text-4xl text-brand-charcoal">Vous vous posez peut-être ces questions</h2>
        </div>

        <div class="space-y-3">
            @foreach ($faqs as $faq)
                <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="rounded-2xl border border-brand-charcoal/10 bg-white overflow-hidden">
                    <button
                        type="button"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                    >
                        <span class="font-medium text-brand-charcoal">{{ $faq['question'] }}</span>
                        <x-heroicon-o-chevron-down
                            class="h-5 w-5 shrink-0 text-brand-rose transition-transform duration-300"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="px-6 pb-5 -mt-1 text-sm text-brand-charcoal/65 leading-relaxed"
                    >
                        {{ $faq['answer'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
