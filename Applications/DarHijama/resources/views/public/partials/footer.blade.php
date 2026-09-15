@php
    $homeUrl = route('dar-hijama.public.home');
    $anchorBase = request()->routeIs('dar-hijama.public.home', 'dar-hijama.public.home.www') ? '' : $homeUrl;
    $contactUrl = $whatsappContactUrl ?? $whatsappBookingUrl;
@endphp
<footer class="border-t border-dar-hijama-ink/5 bg-white">
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:py-20">
        <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-12">
            <div class="sm:col-span-2 lg:col-span-6">
                <a href="{{ $homeUrl }}" class="dh-focus inline-flex items-center gap-2.5 rounded-lg">
                    <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="" width="36" height="36" class="h-9 w-9">
                    <span class="text-lg font-extrabold text-dar-hijama-ink">دار الحجامة</span>
                </a>
                <p class="mt-5 max-w-sm leading-7 text-gray-600">
                    حجامة منزلية بمواعيد منظمة في تونس الكبرى: تونس، أريانة، بن عروس، منوبة.
                </p>
            </div>

            <nav class="lg:col-span-3" aria-label="روابط الفوتر">
                <p class="text-sm font-bold text-dar-hijama-ink">الموقع</p>
                <ul class="mt-5 space-y-3 text-gray-600">
                    <li><a href="{{ $anchorBase }}#services" class="dh-focus rounded hover:text-dar-hijama-green-deep">الخدمات</a></li>
                    <li><a href="{{ route('dar-hijama.articles.index') }}" class="dh-focus rounded hover:text-dar-hijama-green-deep">المقالات</a></li>
                    <li><a href="{{ $anchorBase }}#faq" class="dh-focus rounded hover:text-dar-hijama-green-deep">الأسئلة الشائعة</a></li>
                    <li><a href="{{ $anchorBase }}#contact" class="dh-focus rounded hover:text-dar-hijama-green-deep">اتصل بنا</a></li>
                </ul>
            </nav>

            <div class="lg:col-span-3">
                <p class="text-sm font-bold text-dar-hijama-ink">الحجز</p>
                <ul class="mt-5 space-y-3 text-gray-600">
                    <li><a href="{{ $whatsappBookingUrl }}" target="_blank" rel="noopener" x-data="whatsappCta('footer')" @click="track()" class="dh-focus rounded font-semibold text-dar-hijama-green-deep hover:underline">احجز موعدك</a></li>
                    <li><a href="{{ $contactUrl }}" target="_blank" rel="noopener" x-data="whatsappCta('footer-contact')" @click="track()" class="dh-focus rounded hover:text-dar-hijama-green-deep">واتساب</a></li>
                    <li><a href="{{ route('filament.admin.auth.login') }}" class="dh-focus rounded hover:text-dar-hijama-green-deep">دخول فريق العمل</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-14 flex flex-wrap items-center justify-between gap-4 border-t border-dar-hijama-ink/5 pt-8 text-sm text-gray-500">
            <p>© {{ now()->year }} دار الحجامة. جميع الحقوق محفوظة.</p>
            <p class="font-dar-hijama-latin" dir="ltr">darhijama.tn</p>
        </div>
    </div>
</footer>
