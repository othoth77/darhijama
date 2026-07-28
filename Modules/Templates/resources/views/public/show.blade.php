<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageTitle = "{$template->name} — Modèle d'invitation Notre Jour";
        $pageDescription = $template->description ?: "Prévisualisez le modèle {$template->name} et personnalisez votre invitation digitale.";
    @endphp
    <x-shared.seo-metadata
        :title="$pageTitle"
        :description="\Illuminate\Support\Str::limit($pageDescription, 160)"
        :canonical="route('templates.show', $template->slug)"
        :image="$previewUrl"
    />
    @php
        $structuredData = [
            (chr(64).'context') => 'https://schema.org',
            '@type' => 'Product',
            'name' => $template->name,
            'description' => $pageDescription,
            'image' => $previewUrl,
            'category' => $template->category->name,
            'url' => route('templates.show', $template->slug),
            'offers' => [
                '@type' => 'Offer',
                'price' => config('whatsapp.offer.price'),
                'priceCurrency' => 'TND',
                'availability' => 'https://schema.org/InStock',
            ],
        ];
    @endphp
    <x-shared.structured-data :data="$structuredData" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased">
    <header class="border-b border-brand-charcoal/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="{{ route('landing.index') }}" class="font-display text-2xl">Notre<span class="text-brand-rose"> Jour</span></a>
            <a href="{{ route('templates.index') }}" class="text-sm font-medium hover:text-brand-rose">Tous les modèles</a>
        </div>
    </header>

    <main>
        <section class="mx-auto grid max-w-6xl gap-10 px-6 py-12 lg:grid-cols-[0.9fr_1.1fr] lg:py-20">
            <div>
                @if ($previewUrl)
                    <x-shared.responsive-media
                        :src="$previewUrl"
                        alt="Aperçu du modèle {{ $template->name }}"
                        loading="eager"
                        width="800"
                        height="1000"
                        fetchpriority="high"
                        class="aspect-[4/5] w-full rounded-3xl object-cover shadow-lg"
                    />
                @else
                    <div class="flex aspect-[4/5] items-center justify-center rounded-3xl bg-linear-to-br from-brand-rose/20 via-brand-beige to-brand-ivory p-10 text-center shadow-lg">
                        <span class="font-display text-4xl">{{ $template->name }}</span>
                    </div>
                @endif
            </div>

            <div class="self-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose">{{ $template->category->name }}</p>
                <h1 class="mt-3 font-display text-4xl sm:text-5xl">{{ $template->name }}</h1>
                @if ($template->description)
                    <p class="mt-5 leading-relaxed text-brand-charcoal/70">{{ $template->description }}</p>
                @endif
                <x-shared.whatsapp-cta
                    :href="$whatsappUrl"
                    source="template_detail"
                    :context="['template_id' => $template->id]"
                    class="mt-8 inline-flex rounded-full bg-brand-rose px-7 py-3.5 font-medium text-white"
                >
                    Choisir ce modèle
                </x-shared.whatsapp-cta>
            </div>
        </section>

        <section aria-labelledby="demo-title" class="bg-brand-charcoal px-6 py-16 text-white sm:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs uppercase tracking-[0.25em] text-brand-gold">Démo personnalisable</p>
                <h2 id="demo-title" class="mt-4 font-display text-3xl sm:text-5xl">{{ $demo['groom_name'] }} &amp; {{ $demo['bride_name'] }}</h2>
                <p class="mt-6 text-lg text-white/75">{{ $demo['message'] }}</p>
                <dl class="mt-10 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 p-5">
                        <dt class="text-xs uppercase tracking-widest text-brand-gold">Date</dt>
                        <dd class="mt-2 font-display text-xl">{{ $demo['date'] }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 p-5">
                        <dt class="text-xs uppercase tracking-widest text-brand-gold">Lieu</dt>
                        <dd class="mt-2 font-display text-xl">{{ $demo['venue'] }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    </main>
</body>
</html>
