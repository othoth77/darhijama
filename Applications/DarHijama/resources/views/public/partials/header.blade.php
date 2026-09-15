@php
    $homeUrl = route('dar-hijama.public.home');
    $anchorBase = request()->routeIs('dar-hijama.public.home', 'dar-hijama.public.home.www') ? '' : $homeUrl;
    $navLinks = [
        ['label' => 'الخدمات', 'href' => $anchorBase.'#services'],
        ['label' => 'المقالات', 'href' => route('dar-hijama.articles.index')],
        ['label' => 'الأسئلة الشائعة', 'href' => $anchorBase.'#faq'],
        ['label' => 'اتصل بنا', 'href' => $anchorBase.'#contact'],
    ];
@endphp
<header x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" class="sticky top-0 z-40 border-b border-dar-hijama-ink/5 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8 lg:h-20">
        <a href="{{ $homeUrl }}" class="dh-focus flex items-center gap-2.5 rounded-lg">
            <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="" width="36" height="36" class="h-9 w-9">
            <span class="text-lg font-extrabold text-dar-hijama-ink">دار الحجامة</span>
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="التنقّل الرئيسي">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}" class="dh-focus rounded-full px-4 py-2 text-[0.95rem] font-semibold text-gray-600 transition hover:bg-dar-hijama-mist hover:text-dar-hijama-ink">{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a
                href="{{ $whatsappBookingUrl }}"
                target="_blank" rel="noopener"
                x-data="whatsappCta('header')" @click="track()"
                class="dh-btn dh-btn-primary hidden px-5 py-2.5 text-sm sm:inline-flex"
            >احجز موعدك</a>
            <button
                type="button"
                @click="menuOpen = ! menuOpen"
                aria-expanded="false"
                :aria-expanded="menuOpen ? 'true' : 'false'"
                aria-controls="dh-mobile-nav"
                class="dh-focus grid h-11 w-11 place-items-center rounded-full text-dar-hijama-ink transition hover:bg-dar-hijama-mist md:hidden"
            >
                <span class="sr-only">القائمة</span>
                <svg x-show="! menuOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="menuOpen" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <nav id="dh-mobile-nav" x-show="menuOpen" x-cloak x-transition.opacity class="border-t border-dar-hijama-ink/5 bg-white md:hidden" aria-label="التنقّل على الجوال">
        <ul class="mx-auto max-w-7xl px-5 py-3">
            @foreach ($navLinks as $link)
                <li>
                    <a href="{{ $link['href'] }}" @click="menuOpen = false" class="dh-focus block rounded-xl px-3 py-3 text-base font-semibold text-dar-hijama-ink hover:bg-dar-hijama-mist">{{ $link['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
