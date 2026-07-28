<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-shared.seo-metadata
        title="Modèles d'invitations digitales — Notre Jour"
        description="Découvrez nos modèles d'invitations de mariage digitales, filtrez par style et prévisualisez chaque modèle."
        :canonical="route('templates.index')"
    />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased">
    <a href="#catalogue" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-full focus:bg-brand-charcoal focus:px-5 focus:py-2 focus:text-white">
        Aller au catalogue
    </a>

    <header class="border-b border-brand-charcoal/10 bg-brand-ivory/90">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="{{ route('landing.index') }}" class="font-display text-2xl">Notre<span class="text-brand-rose"> Jour</span></a>
            <x-shared.whatsapp-cta
                :href="$whatsappUrl"
                source="templates_header"
                class="rounded-full bg-brand-rose px-5 py-2.5 text-sm font-medium text-white"
            >
                Nous contacter
            </x-shared.whatsapp-cta>
        </div>
    </header>

    <main id="catalogue" class="mx-auto max-w-6xl px-6 py-14 sm:py-20">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-rose">Collection Notre Jour</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">Nos modèles d'invitations</h1>
            <p class="mt-4 text-brand-charcoal/65">Choisissez une ambiance, découvrez sa démo puis contactez-nous pour la personnaliser.</p>
        </div>

        <form method="GET" action="{{ route('templates.index') }}" role="search" class="mt-10 grid gap-4 rounded-2xl border border-brand-charcoal/10 bg-white/50 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="template-search" class="text-sm font-medium">Rechercher</label>
                <input id="template-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="mt-1 w-full rounded-xl border border-brand-charcoal/15 bg-white px-4 py-2.5" placeholder="Nom ou description">
            </div>
            <div>
                <label for="template-category" class="text-sm font-medium">Catégorie</label>
                <select id="template-category" name="category" class="mt-1 w-full rounded-xl border border-brand-charcoal/15 bg-white px-4 py-2.5">
                    <option value="">Toutes</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="template-sort" class="text-sm font-medium">Trier par</label>
                <select id="template-sort" name="sort" class="mt-1 w-full rounded-xl border border-brand-charcoal/15 bg-white px-4 py-2.5">
                    <option value="featured" @selected(($filters['sort'] ?? 'featured') === 'featured')>Sélection</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Nom</option>
                    <option value="latest" @selected(($filters['sort'] ?? '') === 'latest')>Plus récents</option>
                </select>
            </div>
            <button class="self-end rounded-xl bg-brand-charcoal px-5 py-2.5 font-medium text-white">Filtrer</button>
        </form>

        @if ($templates->isEmpty())
            <x-shared.empty-state class="mt-10" title="Aucun modèle trouvé" description="Essayez une autre recherche ou retirez un filtre." />
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)
                    <article class="overflow-hidden rounded-2xl border border-brand-charcoal/10 bg-white shadow-sm">
                        <a href="{{ route('templates.show', $template->slug) }}" aria-label="Voir le modèle {{ $template->name }}">
                            @if ($template->preview_image_path)
                                <x-shared.responsive-media
                                    :src="$template->preview_url"
                                    alt="Aperçu du modèle {{ $template->name }}"
                                    width="640"
                                    height="800"
                                    class="aspect-[4/5] w-full object-cover"
                                />
                            @else
                                <div class="flex aspect-[4/5] items-center justify-center bg-linear-to-br from-brand-rose/20 via-brand-beige to-brand-ivory p-8 text-center">
                                    <span class="font-display text-2xl">{{ $template->name }}</span>
                                </div>
                            @endif
                        </a>
                        <div class="p-5">
                            <p class="text-xs uppercase tracking-widest text-brand-rose">{{ $template->category->name }}</p>
                            <h2 class="mt-2 font-display text-2xl"><a href="{{ route('templates.show', $template->slug) }}">{{ $template->name }}</a></h2>
                            @if ($template->description)
                                <p class="mt-2 text-sm text-brand-charcoal/65">{{ \Illuminate\Support\Str::limit($template->description, 110) }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">{{ $templates->links() }}</div>
        @endif
    </main>
</body>
</html>
