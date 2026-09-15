<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-dar-hijama-ink antialiased font-dar-hijama-arabic">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-lg focus:bg-dar-hijama-green focus:px-4 focus:py-2 focus:text-white">
        تخطَّ إلى المحتوى
    </a>

    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <a href="{{ route('dar-hijama.public.home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="شعار دار الحجامة" class="h-9 w-9">
                <span class="text-lg font-extrabold text-dar-hijama-ink">دار الحجامة</span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-gray-600 md:flex" aria-label="التنقّل الرئيسي">
                <a href="{{ route('dar-hijama.public.home') }}#services" class="hover:text-dar-hijama-green">خدماتنا</a>
                <a href="{{ route('dar-hijama.articles.index') }}" class="hover:text-dar-hijama-green">المقالات</a>
                <a href="{{ route('dar-hijama.public.home') }}#contact" class="hover:text-dar-hijama-green">تواصل معنا</a>
            </nav>
            <a
                href="{{ $whatsappBookingUrl }}"
                target="_blank" rel="noopener"
                class="rounded-lg bg-dar-hijama-green px-4 py-2 text-sm font-bold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-dar-hijama-green focus:ring-offset-2"
            >
                احجز عبر واتساب
            </a>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <footer class="border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <a href="{{ route('dar-hijama.public.home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="شعار دار الحجامة" class="h-7 w-7">
                    <span class="font-bold text-dar-hijama-ink">دار الحجامة</span>
                </a>
                <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500" aria-label="روابط الفوتر">
                    <a href="{{ route('dar-hijama.public.home') }}#services" class="hover:text-dar-hijama-green">خدماتنا</a>
                    <a href="{{ route('dar-hijama.articles.index') }}" class="hover:text-dar-hijama-green">المقالات</a>
                    <a href="{{ route('filament.admin.auth.login') }}" class="hover:text-dar-hijama-green">دخول فريق العمل</a>
                </nav>
            </div>
            <p class="mt-8 text-xs text-gray-400">© {{ now()->year }} دار الحجامة. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>
