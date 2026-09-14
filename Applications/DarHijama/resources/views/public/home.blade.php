<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="حجامة منزلية بمواعيد منظمة في تونس الكبرى (تونس، أريانة، بن عروس، منوبة). تواصل معنا عبر واتساب لحجز موعدك.">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#16A34A">
    <title>الحجامة المنزلية في تونس الكبرى | دار الحجامة</title>
    <link rel="canonical" href="https://{{ config('applications.dar-hijama.public_hosts.primary') }}/">
    <link rel="icon" href="{{ asset('images/brand/favicon-64.png') }}" type="image/png">

    {{-- Open Graph --}}
    <meta property="og:title" content="الحجامة المنزلية في تونس الكبرى | دار الحجامة">
    <meta property="og:description" content="حجامة منزلية بمواعيد منظمة في تونس الكبرى. تواصل معنا عبر واتساب لحجز موعدك.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_TN">
    <meta property="og:url" content="https://{{ config('applications.dar-hijama.public_hosts.primary') }}/">
    <meta property="og:image" content="{{ asset('images/brand/dar-hijama-piste1-icone-512.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="الحجامة المنزلية في تونس الكبرى | دار الحجامة">
    <meta name="twitter:description" content="حجامة منزلية بمواعيد منظمة في تونس الكبرى.">

    {{-- Schema.org — MedicalBusiness. Only declarative, verifiable facts:
         no invented address, opening hours, ratings or reviews. areaServed
         lists Grand Tunis's four governorates (a real, checkable geographic
         definition — not a coverage claim about the business). --}}
    {{-- "@@" below escapes Blade's real @context directive — a literal
         "@context" key here would otherwise be compiled as that directive
         and break the rest of the page (missing @endcontext). --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "MedicalBusiness",
        "name": "دار الحجامة",
        "description": "حجامة منزلية بمواعيد منظمة في تونس الكبرى.",
        "url": "https://{{ config('applications.dar-hijama.public_hosts.primary') }}/",
        "logo": "{{ asset('images/brand/dar-hijama-piste1-icone-512.png') }}",
        "telephone": "+{{ config('whatsapp.phone_e164') }}",
        "areaServed": [
            {"@@type": "AdministrativeArea", "name": "تونس"},
            {"@@type": "AdministrativeArea", "name": "أريانة"},
            {"@@type": "AdministrativeArea", "name": "بن عروس"},
            {"@@type": "AdministrativeArea", "name": "منوبة"}
        ]
    }
    </script>

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
        {{-- 1. HERO --}}
        <section class="dh-hero-bg relative overflow-hidden" aria-labelledby="hero-title">
            <div class="mx-auto grid max-w-7xl items-center gap-14 px-5 pb-20 pt-10 sm:px-8 sm:pt-14 lg:grid-cols-12 lg:gap-10 lg:pb-28 lg:pt-20">
                <div class="lg:col-span-6">
                    <p class="inline-flex items-center gap-2 rounded-full border border-dar-hijama-green/20 bg-white/80 px-3.5 py-1.5 text-sm font-bold text-dar-hijama-green-deep">
                        <span class="h-1.5 w-1.5 rounded-full bg-dar-hijama-green" aria-hidden="true"></span>
                        خدمة منزلية · تونس الكبرى
                    </p>
                    <h1 id="hero-title" class="mt-6 text-4xl font-black leading-[1.2] tracking-tight text-dar-hijama-ink sm:text-6xl lg:text-[4.25rem]">الحجامة المنزلية <span class="block text-dar-hijama-green-deep">في تونس الكبرى</span></h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600 sm:text-xl">نوصلك إلى منزلك بمواعيد منظمة.</p>

                    <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a
                            href="{{ $whatsappBookingUrl }}"
                            target="_blank" rel="noopener"
                            x-data="whatsappCta('hero')" @click="track()"
                            class="dh-btn dh-btn-primary px-8 py-4 text-lg"
                        >احجز موعدك</a>
                        <a
                            href="{{ $whatsappContactUrl }}"
                            target="_blank" rel="noopener"
                            x-data="whatsappCta('hero-contact')" @click="track()"
                            class="dh-btn dh-btn-secondary px-7 py-4 text-lg"
                        >
                            @include('dar-hijama::public.partials.icon-chat', ['class' => 'h-5 w-5 text-dar-hijama-green-deep'])
                            تواصل معنا عبر واتساب
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    @include('dar-hijama::public.partials.hero-visual')
                </div>
            </div>
        </section>

        {{-- 2. TRUST STRIP --}}
        <section aria-label="مميزات الخدمة" class="border-y border-dar-hijama-ink/5 bg-white">
            <ul class="mx-auto grid max-w-7xl divide-y divide-dar-hijama-ink/5 px-5 sm:grid-cols-3 sm:divide-x sm:divide-y-0 sm:px-8">
                @foreach ([
                    ['title' => 'خدمة منزلية', 'text' => 'زيارة في منزلك', 'icon' => 'm2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
                    ['title' => 'مواعيد منظمة', 'text' => 'موعد يُؤكَّد معك مسبقًا', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
                    ['title' => 'تونس الكبرى', 'text' => 'تونس · أريانة · بن عروس · منوبة', 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                ] as $point)
                    <li class="flex items-center gap-4 py-6 sm:justify-center sm:px-6 lg:py-8">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-dar-hijama-mint text-dar-hijama-green-deep">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $point['icon'] }}"/></svg>
                        </span>
                        <div>
                            <p class="text-lg font-bold text-dar-hijama-ink">{{ $point['title'] }}</p>
                            <p class="text-sm text-gray-500">{{ $point['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- 3. HOW IT WORKS --}}
        <section id="how-it-works" class="bg-dar-hijama-mist" aria-labelledby="how-title">
            <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:py-28">
                <div class="dh-reveal max-w-2xl">
                    <p class="dh-eyebrow">خطوات بسيطة</p>
                    <h2 id="how-title" class="mt-3 text-3xl font-black text-dar-hijama-ink sm:text-5xl">كيف تتم الخدمة؟</h2>
                </div>
                <ol class="mt-14 grid gap-10 md:grid-cols-3 md:gap-8">
                    @foreach ([
                        ['title' => 'احجز موعدك', 'text' => 'راسلنا عبر واتساب واختر الوقت المناسب لك.'],
                        ['title' => 'ننسق معك الموعد', 'text' => 'نؤكد معك التاريخ والساعة قبل الزيارة.'],
                        ['title' => 'نصل إلى منزلك', 'text' => 'يصلك فريقنا في الموعد المتفق عليه.'],
                    ] as $i => $step)
                        <li class="dh-reveal relative border-t border-dar-hijama-ink/10 pt-8" style="transition-delay: {{ $i * 120 }}ms">
                            <span class="absolute -top-px start-0 h-0.5 w-16 bg-dar-hijama-green" aria-hidden="true"></span>
                            <span class="font-dar-hijama-latin text-5xl font-extrabold text-dar-hijama-green" aria-hidden="true">0{{ $i + 1 }}</span>
                            <h3 class="mt-5 text-xl font-bold text-dar-hijama-ink sm:text-2xl">{{ $step['title'] }}</h3>
                            <p class="mt-3 max-w-xs leading-7 text-gray-600">{{ $step['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- 4. SERVICE --}}
        <section id="services" aria-labelledby="service-title">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-20 sm:px-8 lg:grid-cols-2 lg:gap-20 lg:py-28">
                <div class="dh-reveal">
                    @include('dar-hijama::public.partials.service-visual')
                </div>
                <div class="dh-reveal">
                    <p class="dh-eyebrow">الخدمة</p>
                    <h2 id="service-title" class="mt-3 text-3xl font-black text-dar-hijama-ink sm:text-5xl">الحجامة المنزلية</h2>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                        جلسة حجامة تتم في منزلك بدل التنقّل إلى مركز. تتواصل معنا عبر واتساب، نتفق معك على الموعد، ثم يصلك فريقنا بمعدات معقّمة أحادية الاستخدام.
                    </p>
                    <ul class="mt-8 space-y-4">
                        @foreach ([
                            'زيارة منزلية في تونس الكبرى',
                            'موعد يُؤكَّد معك قبل الزيارة',
                            'معدات معقّمة أحادية الاستخدام',
                            'بياناتك محفوظة ضمن صلاحيات وصول محدودة',
                        ] as $feature)
                            <li class="flex items-start gap-3 text-dar-hijama-ink">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-6 w-6 shrink-0 text-dar-hijama-green" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span class="font-semibold">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-10">
                        <a
                            href="{{ $whatsappBookingUrl }}"
                            target="_blank" rel="noopener"
                            x-data="whatsappCta('service')" @click="track()"
                            class="dh-btn dh-btn-primary px-8 py-4 text-lg"
                        >احجز موعدك</a>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. COVERAGE / LOCAL SEO --}}
        <section id="coverage" class="bg-gradient-to-b from-white to-dar-hijama-mist" aria-labelledby="coverage-title">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-8 lg:grid-cols-12 lg:items-center lg:py-28">
                <div class="dh-reveal lg:col-span-5">
                    <p class="dh-eyebrow">منطقة الخدمة</p>
                    <h2 id="coverage-title" class="mt-3 text-3xl font-black text-dar-hijama-ink sm:text-5xl">نخدم تونس الكبرى</h2>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        نقدّم الحجامة المنزلية في ولايات تونس الكبرى الأربع. راسلنا عبر واتساب لتأكيد التغطية في منطقتك بالتحديد.
                    </p>
                </div>
                <ul class="dh-reveal grid grid-cols-2 gap-px overflow-hidden rounded-3xl border border-dar-hijama-ink/10 bg-dar-hijama-ink/10 lg:col-span-7">
                    @foreach (['تونس', 'أريانة', 'بن عروس', 'منوبة'] as $governorate)
                        <li class="flex flex-col gap-4 bg-white p-6 sm:p-8">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-7 w-7 text-dar-hijama-turquoise" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            <div>
                                <p class="text-sm text-gray-500">ولاية</p>
                                <p class="text-2xl font-bold text-dar-hijama-ink sm:text-3xl">{{ $governorate }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- 6. LATEST ARTICLES --}}
        @if ($latestArticles->isNotEmpty())
            <section id="articles" aria-labelledby="articles-title">
                <div class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:py-28">
                    <div class="dh-reveal flex flex-wrap items-end justify-between gap-6">
                        <div>
                            <p class="dh-eyebrow">المقالات</p>
                            <h2 id="articles-title" class="mt-3 text-3xl font-black text-dar-hijama-ink sm:text-5xl">أحدث المقالات</h2>
                        </div>
                        <a href="{{ route('dar-hijama.articles.index') }}" class="dh-focus inline-flex items-center gap-2 rounded font-bold text-dar-hijama-green-deep hover:underline">
                            كل المقالات
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        </a>
                    </div>
                    <div class="mt-12 grid gap-x-8 gap-y-14 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($latestArticles as $article)
                            @include('dar-hijama::public.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- 7. FAQ --}}
        <section id="faq" class="bg-dar-hijama-mist" aria-labelledby="faq-title">
            <div class="mx-auto grid max-w-7xl gap-12 px-5 py-20 sm:px-8 lg:grid-cols-12 lg:py-28">
                <div class="dh-reveal lg:col-span-4">
                    <p class="dh-eyebrow">الأسئلة الشائعة</p>
                    <h2 id="faq-title" class="mt-3 text-3xl font-black text-dar-hijama-ink sm:text-5xl">أسئلة يتكرر طرحها</h2>
                    <p class="mt-6 leading-8 text-gray-600">
                        لم تجد إجابتك؟
                        <a href="{{ $whatsappContactUrl }}" target="_blank" rel="noopener" x-data="whatsappCta('faq')" @click="track()" class="dh-focus rounded font-bold text-dar-hijama-green-deep underline-offset-4 hover:underline">راسلنا عبر واتساب</a>
                    </p>
                </div>
                <div class="lg:col-span-8" x-data="{ open: null }">
                    <div class="divide-y divide-dar-hijama-ink/10 rounded-3xl border border-dar-hijama-ink/10 bg-white px-2 sm:px-4">
                        @foreach ([
                            ['q' => 'ما الفرق بين الحجامة الجافة والرطبة؟', 'a' => 'الحجامة الجافة تعتمد على الشفط فقط دون سحب الدم، أما الرطبة فتتضمن سحب الدم الراكد بتقنية معقّمة. نوضّح لك الأنسب لحالتك خلال الاستشارة الأولية.'],
                            ['q' => 'هل تصلون إلى كل مناطق تونس الكبرى؟', 'a' => 'نغطي مناطق واسعة بزيارة منزلية. راسلنا عبر واتساب لتأكيد التغطية في منطقتك بالتحديد.'],
                            ['q' => 'كيف يتم تأكيد الموعد؟', 'a' => 'بعد التواصل عبر واتساب، يقوم فريقنا بتأكيد التاريخ والساعة معك مباشرة قبل الزيارة.'],
                            ['q' => 'هل بياناتي وملفي الصحي محفوظان بخصوصية؟', 'a' => 'نعم، تُحفظ بيانات كل مريض ضمن صلاحيات وصول محدودة داخل النظام، ولا تُشارك خارج فريق المتابعة.'],
                        ] as $i => $item)
                            <div>
                                <h3>
                                    <button
                                        type="button"
                                        id="faq-q-{{ $i }}"
                                        aria-controls="faq-a-{{ $i }}"
                                        aria-expanded="false"
                                        :aria-expanded="open === {{ $i }} ? 'true' : 'false'"
                                        @click="open = open === {{ $i }} ? null : {{ $i }}"
                                        class="dh-focus flex w-full items-center justify-between gap-6 rounded-2xl px-4 py-5 text-start text-lg font-bold text-dar-hijama-ink"
                                    >
                                        <span>{{ $item['q'] }}</span>
                                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-dar-hijama-mint text-dar-hijama-green-deep transition duration-300" :class="open === {{ $i }} && 'rotate-45'" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </span>
                                    </button>
                                </h3>
                                <div id="faq-a-{{ $i }}" role="region" aria-labelledby="faq-q-{{ $i }}" x-show="open === {{ $i }}" x-cloak x-transition.opacity class="px-4 pb-6 leading-8 text-gray-600">
                                    {{ $item['a'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- 8. FINAL CTA --}}
        <section id="contact" class="px-5 py-20 sm:px-8 lg:py-28" aria-labelledby="cta-title">
            <div class="dh-reveal dh-cta-bg relative mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-dar-hijama-green/15 px-6 py-16 text-center sm:px-12 sm:py-20">
                <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="" width="320" height="320" loading="lazy" class="pointer-events-none absolute -bottom-16 -start-16 h-80 w-80 opacity-[0.06]">
                <h2 id="cta-title" class="relative text-3xl font-black text-dar-hijama-ink sm:text-5xl">جاهز لحجز موعدك؟</h2>
                <p class="relative mx-auto mt-5 max-w-xl text-lg leading-8 text-gray-600">الحجامة المنزلية في تونس الكبرى بمواعيد منظمة.</p>
                <div class="relative mt-10 flex flex-col justify-center gap-3 sm:flex-row">
                    <a
                        href="{{ $whatsappBookingUrl }}"
                        target="_blank" rel="noopener"
                        x-data="whatsappCta('final')" @click="track()"
                        class="dh-btn dh-btn-primary px-10 py-4 text-lg"
                    >احجز موعدك</a>
                    <a
                        href="{{ $whatsappContactUrl }}"
                        target="_blank" rel="noopener"
                        x-data="whatsappCta('final-contact')" @click="track()"
                        class="dh-btn dh-btn-secondary px-8 py-4 text-lg"
                    >
                        @include('dar-hijama::public.partials.icon-chat', ['class' => 'h-5 w-5 text-dar-hijama-green-deep'])
                        واتساب
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('dar-hijama::public.partials.footer')
    @include('dar-hijama::public.partials.mobile-cta')
</body>
</html>
