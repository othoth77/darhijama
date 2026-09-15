<div class="fixed inset-x-0 bottom-0 z-40 border-t border-dar-hijama-ink/10 bg-white/95 px-4 pt-3 backdrop-blur-md md:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
    <div class="flex gap-2">
        <a
            href="{{ $whatsappBookingUrl }}"
            target="_blank" rel="noopener"
            x-data="whatsappCta('mobile-bar')" @click="track()"
            class="dh-btn dh-btn-primary flex-1 py-3.5"
        >احجز موعدك</a>
        <a
            href="{{ $whatsappContactUrl ?? $whatsappBookingUrl }}"
            target="_blank" rel="noopener"
            x-data="whatsappCta('mobile-bar-contact')" @click="track()"
            class="dh-btn dh-btn-secondary w-14 shrink-0 px-0 py-3.5"
        >
            <span class="sr-only">تواصل معنا عبر واتساب</span>
            @include('dar-hijama::public.partials.icon-chat', ['class' => 'h-6 w-6'])
        </a>
    </div>
</div>
