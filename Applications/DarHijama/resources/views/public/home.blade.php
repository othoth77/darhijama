<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="دار الحجامة — رعاية مهنية ومواعيد منظمة للحجامة في تونس، بزيارة منزلية وفريق مختص.">
    <meta name="keywords" content="حجامة, دار الحجامة, حجامة تونس, حجامة في المنزل, حجامة نبوية">
    <meta name="robots" content="index, follow">
    <title>دار الحجامة | رعاية مهنية ومواعيد منظمة في تونس</title>
    <link rel="canonical" href="https://{{ config('applications.dar-hijama.public_hosts.primary') }}/">
    <link rel="icon" href="{{ asset('images/brand/favicon-64.png') }}" type="image/png">

    {{-- Open Graph --}}
    <meta property="og:title" content="دار الحجامة | رعاية مهنية ومواعيد منظمة في تونس">
    <meta property="og:description" content="دار الحجامة — رعاية مهنية ومواعيد منظمة للحجامة في تونس، بزيارة منزلية وفريق مختص.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_TN">
    <meta property="og:url" content="https://{{ config('applications.dar-hijama.public_hosts.primary') }}/">
    <meta property="og:image" content="{{ asset('images/brand/dar-hijama-piste1-icone-512.png') }}">

    {{-- Schema.org — LocalBusiness. Aucun avis/note inventé : uniquement des
         faits déclaratifs (nom, description, zone de service). --}}
    {{-- "@@" below escapes Blade's real @context directive — a literal
         "@context" key here would otherwise be compiled as that directive
         and break the rest of the page (missing @endcontext). --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "MedicalBusiness",
        "name": "دار الحجامة",
        "description": "رعاية مهنية ومواعيد منظمة للحجامة في تونس، بزيارة منزلية.",
        "url": "https://{{ config('applications.dar-hijama.public_hosts.primary') }}/",
        "areaServed": "تونس"
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-dar-hijama-ink antialiased font-dar-hijama-arabic">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-lg focus:bg-dar-hijama-green focus:px-4 focus:py-2 focus:text-white">
        تخطَّ إلى المحتوى
    </a>

    {{-- HEADER --}}
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <a href="#main" class="flex items-center gap-2">
                <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="شعار دار الحجامة" class="h-9 w-9">
                <span class="text-lg font-extrabold text-dar-hijama-ink">دار الحجامة</span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-gray-600 md:flex" aria-label="التنقّل الرئيسي">
                <a href="#services" class="hover:text-dar-hijama-green">خدماتنا</a>
                <a href="#how-it-works" class="hover:text-dar-hijama-green">كيف نعمل</a>
                <a href="#faq" class="hover:text-dar-hijama-green">الأسئلة الشائعة</a>
                <a href="#contact" class="hover:text-dar-hijama-green">تواصل معنا</a>
            </nav>
            <a
                href="{{ $whatsappBookingUrl }}"
                target="_blank" rel="noopener"
                x-data="whatsappCta('header')" @click="track()"
                class="rounded-lg bg-dar-hijama-green px-4 py-2 text-sm font-bold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-dar-hijama-green focus:ring-offset-2"
            >
                احجز عبر واتساب
            </a>
        </div>
    </header>

    <main id="main">
        {{-- HERO --}}
        <section class="border-b border-gray-100 bg-gradient-to-b from-green-50/60 to-white">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 py-16 lg:grid-cols-2 lg:items-center lg:py-24">
                <div>
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1 text-xs font-bold tracking-widest text-dar-hijama-green">
                        دار الحجامة
                    </p>
                    <h1 class="max-w-xl text-4xl font-extrabold leading-tight text-dar-hijama-ink sm:text-5xl">
                        رعاية مهنية، متابعة واضحة، ومواعيد منظمة
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                        دار الحجامة منصة مستقلة لإدارة خدمات الحجامة والمرضى والمواعيد،
                        مع زيارة منزلية وفريق مختص واحترام كامل للخصوصية.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <a
                            href="{{ $whatsappBookingUrl }}"
                            target="_blank" rel="noopener"
                            x-data="whatsappCta('hero')" @click="track()"
                            class="rounded-lg bg-dar-hijama-green px-6 py-3 font-bold text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-dar-hijama-green focus:ring-offset-2"
                        >
                            احجز موعدًا عبر واتساب
                        </a>
                        <a
                            href="{{ route('filament.admin.auth.login') }}"
                            class="rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition hover:border-dar-hijama-green hover:text-dar-hijama-green focus:outline-none focus:ring-2 focus:ring-dar-hijama-green focus:ring-offset-2"
                        >
                            دخول فريق العمل
                        </a>
                    </div>
                </div>

                <div class="flex items-center justify-center rounded-2xl border border-gray-100 bg-white p-10 shadow-sm">
                    <img src="{{ asset('images/brand/dar-hijama-piste1-logo-principal.svg') }}" alt="شعار دار الحجامة" class="h-48 w-48 sm:h-56 sm:w-56">
                </div>
            </div>
        </section>

        {{-- SERVICES --}}
        <section id="services" class="mx-auto max-w-6xl px-6 py-16 sm:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-dar-hijama-turquoise">خدماتنا</span>
                <h2 class="mt-2 text-3xl font-extrabold text-dar-hijama-ink">رعاية متكاملة من أول تواصل حتى المتابعة</h2>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-3">
                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-green-50 text-dar-hijama-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-dar-hijama-ink">مواعيد منظمة تناسبك</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">تحديد موعد الاستشارة الأولية أو جلسة الحجامة بما يناسب وقتك، ومتابعة الحجز من التأكيد حتى الزيارة.</p>
                </article>
                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-green-50 text-dar-hijama-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11 12 4l9 7M5 10v10h14V10"/></svg>
                    </div>
                    <h3 class="font-bold text-dar-hijama-ink">زيارة منزلية</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">يصلك فريقنا في منزلك بمعدات معقّمة أحادية الاستخدام، دون الحاجة للتنقّل.</p>
                </article>
                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-green-50 text-dar-hijama-green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-dar-hijama-ink">متابعة ومصداقية</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">توثيق كل جلسة ومتابعة ما بعدها ضمن ملف خاص يحترم خصوصية بياناتك.</p>
                </article>
            </div>
        </section>

        {{-- HOW IT WORKS --}}
        <section id="how-it-works" class="border-y border-gray-100 bg-gray-50/60">
            <div class="mx-auto max-w-6xl px-6 py-16 sm:py-24">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-xs font-bold uppercase tracking-widest text-dar-hijama-turquoise">كيف نعمل</span>
                    <h2 class="mt-2 text-3xl font-extrabold text-dar-hijama-ink">أربع خطوات بسيطة وواضحة</h2>
                </div>
                <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <li class="relative rounded-xl bg-white p-6 shadow-sm">
                        <span class="text-sm font-black text-dar-hijama-turquoise">01</span>
                        <h3 class="mt-2 font-bold text-dar-hijama-ink">تواصل معنا</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">تراسلنا عبر واتساب لتحديد احتياجك.</p>
                    </li>
                    <li class="relative rounded-xl bg-white p-6 shadow-sm">
                        <span class="text-sm font-black text-dar-hijama-turquoise">02</span>
                        <h3 class="mt-2 font-bold text-dar-hijama-ink">استشارة أولية</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">نفهم حالتك ونحدد نوع الجلسة الأنسب لك.</p>
                    </li>
                    <li class="relative rounded-xl bg-white p-6 shadow-sm">
                        <span class="text-sm font-black text-dar-hijama-turquoise">03</span>
                        <h3 class="mt-2 font-bold text-dar-hijama-ink">جلسة الحجامة</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">يصلك فريقنا في موعده لإجراء الجلسة في منزلك.</p>
                    </li>
                    <li class="relative rounded-xl bg-white p-6 shadow-sm">
                        <span class="text-sm font-black text-dar-hijama-turquoise">04</span>
                        <h3 class="mt-2 font-bold text-dar-hijama-ink">متابعة</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">نتابع معك بعد الجلسة ونحدد الخطوة التالية إن لزم.</p>
                    </li>
                </ol>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="faq" class="mx-auto max-w-4xl px-6 py-16 sm:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-dar-hijama-turquoise">الأسئلة الشائعة</span>
                <h2 class="mt-2 text-3xl font-extrabold text-dar-hijama-ink">أسئلة يتكرر طرحها</h2>
            </div>
            <div class="mt-10 divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white" x-data="{ open: null }">
                @foreach ([
                    ['q' => 'ما الفرق بين الحجامة الجافة والرطبة؟', 'a' => 'الحجامة الجافة تعتمد على الشفط فقط دون سحب الدم، أما الرطبة فتتضمن سحب الدم الراكد بتقنية معقّمة. نوضّح لك الأنسب لحالتك خلال الاستشارة الأولية.'],
                    ['q' => 'هل تصلون إلى كل مناطق تونس الكبرى؟', 'a' => 'نغطي مناطق واسعة بزيارة منزلية. راسلنا عبر واتساب لتأكيد التغطية في منطقتك بالتحديد.'],
                    ['q' => 'كيف يتم تأكيد الموعد؟', 'a' => 'بعد التواصل عبر واتساب، يقوم فريقنا بتأكيد التاريخ والساعة معك مباشرة قبل الزيارة.'],
                    ['q' => 'هل بياناتي وملفي الصحي محفوظان بخصوصية؟', 'a' => 'نعم، تُحفظ بيانات كل مريض ضمن صلاحيات وصول محدودة داخل النظام، ولا تُشارك خارج فريق المتابعة.'],
                ] as $i => $item)
                    <div>
                        <button
                            type="button"
                            @click="open = open === {{ $i }} ? null : {{ $i }}"
                            :aria-expanded="open === {{ $i }}"
                            class="flex w-full items-center justify-between px-6 py-4 text-right font-semibold text-dar-hijama-ink"
                        >
                            {{ $item['q'] }}
                            <span x-text="open === {{ $i }} ? '−' : '+'" class="text-dar-hijama-turquoise"></span>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak class="px-6 pb-4 text-sm leading-6 text-gray-600">
                            {{ $item['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- CONTACT / CTA --}}
        <section id="contact" class="border-t border-gray-100 bg-gradient-to-l from-dar-hijama-green to-dar-hijama-turquoise">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6 px-6 py-14 text-white">
                <div>
                    <h2 class="text-2xl font-extrabold">جاهزون للتواصل معك</h2>
                    <p class="mt-2 max-w-md text-white/90">راسلنا عبر واتساب لتحديد موعدك أو الاستفسار عن خدماتنا.</p>
                </div>
                <a
                    href="{{ $whatsappBookingUrl }}"
                    target="_blank" rel="noopener"
                    x-data="whatsappCta('contact')" @click="track()"
                    class="rounded-lg bg-white px-6 py-3 font-bold text-dar-hijama-green transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-dar-hijama-green"
                >
                    تواصل عبر واتساب
                </a>
            </div>
        </section>
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-gray-100 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex flex-wrap items-center justify-between gap-6">
                <a href="#main" class="flex items-center gap-2">
                    <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="شعار دار الحجامة" class="h-7 w-7">
                    <span class="font-bold text-dar-hijama-ink">دار الحجامة</span>
                </a>
                <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500" aria-label="روابط الفوتر">
                    <a href="#services" class="hover:text-dar-hijama-green">خدماتنا</a>
                    <a href="#how-it-works" class="hover:text-dar-hijama-green">كيف نعمل</a>
                    <a href="#faq" class="hover:text-dar-hijama-green">الأسئلة الشائعة</a>
                    <a href="{{ route('filament.admin.auth.login') }}" class="hover:text-dar-hijama-green">دخول فريق العمل</a>
                </nav>
            </div>
            <p class="mt-8 text-xs text-gray-400">© {{ now()->year }} دار الحجامة. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>
