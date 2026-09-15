{{-- A real photo dropped in public/images/dar-hijama/hero.webp (optional .avif) replaces the inline illustration without a code change. --}}
@php($heroPhoto = file_exists(public_path('images/dar-hijama/hero.webp')))
<div class="relative mx-auto w-full max-w-xl lg:max-w-none">
    <div class="relative overflow-hidden rounded-[2rem] border border-dar-hijama-green/10 bg-gradient-to-br from-dar-hijama-mint via-white to-teal-50 shadow-[0_40px_90px_-50px_rgba(15,118,110,0.45)]">
        @if ($heroPhoto)
            <picture>
                @if (file_exists(public_path('images/dar-hijama/hero.avif')))
                    <source srcset="{{ asset('images/dar-hijama/hero.avif') }}" type="image/avif">
                @endif
                <img src="{{ asset('images/dar-hijama/hero.webp') }}" alt="تحضير جلسة حجامة منزلية" width="1120" height="960" fetchpriority="high" decoding="async" class="aspect-[7/6] w-full object-cover">
            </picture>
        @else
            <svg viewBox="0 0 560 480" width="560" height="480" class="h-auto w-full" role="img" aria-label="رسم توضيحي لأدوات حجامة مرتبة على صينية في منزل هادئ">
                <defs>
                    <linearGradient id="dh-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#CCFBF1"/><stop offset="1" stop-color="#FFFFFF"/></linearGradient>
                    <linearGradient id="dh-glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFFFFF"/><stop offset="1" stop-color="#CCFBF1"/></linearGradient>
                    <filter id="dh-soft" x="-20%" y="-20%" width="140%" height="180%"><feDropShadow dx="0" dy="12" stdDeviation="14" flood-color="#0F766E" flood-opacity="0.14"/></filter>
                </defs>
                <circle cx="280" cy="180" r="140" fill="#16A34A" opacity="0.05"/>
                <path d="M150 360V190a130 130 0 0 1 260 0v170Z" fill="url(#dh-sky)" stroke="#14B8A6" stroke-opacity="0.25" stroke-width="2"/>
                <path d="M152 300c40-26 78-30 118-14s84 14 138-10v84H152Z" fill="#16A34A" opacity="0.10"/>
                <path d="M280 62v298M150 220h260" stroke="#14B8A6" stroke-opacity="0.18" stroke-width="2"/>
                <path d="M470 300c-6-40 6-80 30-104-4 36-12 72-30 104Z" fill="#16A34A" opacity="0.85"/>
                <path d="M466 300c-26-26-40-60-36-92 22 22 34 56 36 92Z" fill="#16A34A" opacity="0.55"/>
                <path d="M474 302c14-30 38-48 66-52-14 26-36 44-66 52Z" fill="#14B8A6" opacity="0.6"/>
                <rect x="444" y="296" width="56" height="64" rx="14" fill="#FFFFFF" stroke="#2F3437" stroke-opacity="0.08" stroke-width="2"/>
                <rect x="40" y="360" width="480" height="16" rx="8" fill="#2F3437" opacity="0.07"/>
                <rect x="62" y="322" width="84" height="38" rx="12" fill="#DCFCE7"/>
                <rect x="70" y="304" width="68" height="22" rx="9" fill="#BBF7D0"/>
                <path d="M70 340h76" stroke="#16A34A" stroke-opacity="0.25" stroke-width="2"/>
                <g filter="url(#dh-soft)"><rect x="176" y="318" width="236" height="42" rx="21" fill="#FFFFFF"/></g>
                <g stroke="#14B8A6" stroke-width="2.5">
                    <path d="M212 326c0-34 8-58 24-58s24 24 24 58Z" fill="url(#dh-glass)"/>
                    <path d="M266 326c0-44 10-72 28-72s28 28 28 72Z" fill="url(#dh-glass)"/>
                    <path d="M328 326c0-34 8-58 24-58s24 24 24 58Z" fill="url(#dh-glass)"/>
                </g>
                <rect x="208" y="322" width="56" height="8" rx="4" fill="#14B8A6" opacity="0.35"/>
                <rect x="262" y="322" width="64" height="8" rx="4" fill="#14B8A6" opacity="0.35"/>
                <rect x="324" y="322" width="56" height="8" rx="4" fill="#14B8A6" opacity="0.35"/>
                <g stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" fill="none">
                    <path d="M226 302c0-10 3-18 7-22"/>
                    <path d="M282 298c0-14 4-24 8-29"/>
                    <path d="M342 302c0-10 3-18 7-22"/>
                </g>
                <circle cx="120" cy="140" r="5" fill="#14B8A6" opacity="0.35"/>
                <circle cx="96" cy="176" r="3" fill="#16A34A" opacity="0.35"/>
                <circle cx="470" cy="120" r="4" fill="#14B8A6" opacity="0.3"/>
            </svg>
        @endif
    </div>

    <div class="dh-float absolute -top-5 start-3 flex items-center gap-3 rounded-2xl border border-dar-hijama-ink/5 bg-white/95 py-2.5 ps-2.5 pe-4 shadow-lg shadow-teal-900/5 sm:start-8" aria-hidden="true">
        <span class="grid h-9 w-9 place-items-center rounded-full bg-dar-hijama-mint text-dar-hijama-green-deep">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
        </span>
        <span class="text-sm font-bold text-dar-hijama-ink">زيارة منزلية</span>
    </div>
    <div class="dh-float absolute -bottom-5 end-3 flex items-center gap-3 rounded-2xl border border-dar-hijama-ink/5 bg-white/95 py-2.5 ps-2.5 pe-4 shadow-lg shadow-teal-900/5 sm:end-8" style="animation-delay: -3s" aria-hidden="true">
        <span class="grid h-9 w-9 place-items-center rounded-full bg-teal-50 text-dar-hijama-teal-deep">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
        </span>
        <span class="text-sm font-bold text-dar-hijama-ink">مواعيد منظمة</span>
    </div>
</div>
