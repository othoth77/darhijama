<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-shared.seo-metadata
        title="Notre Jour — Invitations de mariage digitales"
        description="Votre mariage mérite une invitation aussi unique que votre histoire. Invitation digitale élégante et personnalisée, à partir de {{ $offerPrice }} {{ $offerCurrency }}."
        :canonical="route('landing.index')"
    />
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Notre Jour — Invitations digitales',
            'description' => 'Création d’invitations digitales de mariage personnalisées.',
            'url' => route('landing.index'),
            'areaServed' => 'TN',
            'offers' => [
                '@type' => 'Offer',
                'price' => $offerPrice,
                'priceCurrency' => 'TND',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased selection:bg-brand-rose/20">

    @include('landing::partials.header')

    <main>
        @include('landing::partials.hero')
        @include('landing::partials.pricing')
        @include('landing::partials.features')
        @include('landing::partials.how-it-works')
        @include('landing::partials.gallery')
        @include('landing::partials.faq')
        @include('landing::partials.cta')
    </main>

    @include('landing::partials.footer')

</body>
</html>
