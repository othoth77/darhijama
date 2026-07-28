<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-shared.seo-metadata
        title="Mentions légales — Notre Jour"
        description="Mentions légales du site Notre Jour."
        :canonical="route('legal.mentions')"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased">
    <main class="mx-auto max-w-3xl px-6 py-16 sm:py-24">
        <a href="{{ route('landing.index') }}" class="text-sm font-medium text-brand-rose">← Retour à l'accueil</a>
        <h1 class="mt-8 font-display text-4xl sm:text-5xl">Mentions légales</h1>
        <div class="mt-10 space-y-8 leading-relaxed text-brand-charcoal/75">
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Éditeur</h2>
                <p class="mt-2">Le site Notre Jour présente un service de création d'invitations digitales de mariage.</p>
            </section>
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Contact</h2>
                <p class="mt-2">Pour toute question relative au site ou à son contenu, contactez Notre Jour via WhatsApp.</p>
                <x-shared.whatsapp-cta :href="$whatsappUrl" source="legal_contact" class="mt-4 inline-flex rounded-full bg-brand-charcoal px-5 py-2.5 text-white">Nous contacter</x-shared.whatsapp-cta>
            </section>
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Propriété intellectuelle</h2>
                <p class="mt-2">Les textes, visuels, modèles et éléments graphiques présents sur ce site sont protégés. Toute reproduction non autorisée est interdite.</p>
            </section>
        </div>
    </main>
</body>
</html>
