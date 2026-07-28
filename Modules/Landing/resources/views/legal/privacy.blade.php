<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-shared.seo-metadata
        title="Politique de confidentialité — Notre Jour"
        description="Politique de confidentialité et données collectées par Notre Jour."
        :canonical="route('legal.privacy')"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased">
    <main class="mx-auto max-w-3xl px-6 py-16 sm:py-24">
        <a href="{{ route('landing.index') }}" class="text-sm font-medium text-brand-rose">← Retour à l'accueil</a>
        <h1 class="mt-8 font-display text-4xl sm:text-5xl">Politique de confidentialité</h1>
        <div class="mt-10 space-y-8 leading-relaxed text-brand-charcoal/75">
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Données collectées</h2>
                <p class="mt-2">Le site conserve uniquement les informations nécessaires aux demandes, réponses RSVP et mesures techniques d'audience. Les visiteurs sont pseudonymisés pour éviter le stockage de leur adresse IP et de leur user-agent bruts dans les données analytics.</p>
            </section>
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Finalités</h2>
                <p class="mt-2">Les données servent à fournir le service demandé, sécuriser la plateforme, mesurer les pages consultées et améliorer l'expérience.</p>
            </section>
            <section>
                <h2 class="font-display text-2xl text-brand-charcoal">Vos demandes</h2>
                <p class="mt-2">Vous pouvez demander l'accès, la rectification ou la suppression de vos informations en contactant Notre Jour.</p>
                <x-shared.whatsapp-cta :href="$whatsappUrl" source="privacy_contact" class="mt-4 inline-flex rounded-full bg-brand-charcoal px-5 py-2.5 text-white">Nous contacter</x-shared.whatsapp-cta>
            </section>
        </div>
    </main>
</body>
</html>
