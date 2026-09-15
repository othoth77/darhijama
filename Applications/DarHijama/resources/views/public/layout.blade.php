<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#16A34A">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ ($noindex ?? false) ? 'noindex' : 'index' }}, {{ ($nofollow ?? false) ? 'nofollow' : 'follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ asset('images/brand/favicon-64.png') }}" type="image/png">

    <meta property="og:title" content="{{ $socialTitle ?? $seoTitle }}">
    <meta property="og:description" content="{{ $socialDescription ?? $metaDescription }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="ar_TN">
    @isset($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    @endisset

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $socialTitle ?? $seoTitle }}">
    <meta name="twitter:description" content="{{ $socialDescription ?? $metaDescription }}">

    @isset($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endisset

    {{-- fonts.bunny.net is the only font host the production CSP allows (style-src/font-src). --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700,800,900|manrope:700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white pb-20 font-dar-hijama-arabic text-dar-hijama-ink antialiased md:pb-0">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-lg focus:bg-dar-hijama-green-deep focus:px-4 focus:py-2 focus:text-white">
        تخطَّ إلى المحتوى
    </a>

    @include('dar-hijama::public.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('dar-hijama::public.partials.footer')
    @include('dar-hijama::public.partials.mobile-cta')
</body>
</html>
