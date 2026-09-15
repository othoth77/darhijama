@php($servicePhoto = file_exists(public_path('images/dar-hijama/service.webp')))
<div class="overflow-hidden rounded-[2rem] border border-dar-hijama-green/10 bg-gradient-to-br from-teal-50 via-white to-dar-hijama-mint">
    @if ($servicePhoto)
        <picture>
            @if (file_exists(public_path('images/dar-hijama/service.avif')))
                <source srcset="{{ asset('images/dar-hijama/service.avif') }}" type="image/avif">
            @endif
            <img src="{{ asset('images/dar-hijama/service.webp') }}" alt="خدمة الحجامة المنزلية" width="1040" height="880" loading="lazy" decoding="async" class="aspect-[13/11] w-full object-cover">
        </picture>
    @else
        <svg viewBox="0 0 520 440" width="520" height="440" class="h-auto w-full" role="img" aria-label="رسم توضيحي لوصول الخدمة إلى باب المنزل">
            <defs>
                <linearGradient id="dh-door" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#16A34A"/><stop offset="1" stop-color="#14B8A6"/></linearGradient>
                <filter id="dh-soft-2" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="14" stdDeviation="16" flood-color="#0F766E" flood-opacity="0.14"/></filter>
            </defs>
            <ellipse cx="270" cy="372" rx="220" ry="26" fill="#2F3437" opacity="0.05"/>
            <g filter="url(#dh-soft-2)"><path d="M170 360V206l90-70 90 70v154Z" fill="#FFFFFF"/></g>
            <path d="M170 206l90-70 90 70" fill="none" stroke="#16A34A" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="236" y="190" width="48" height="40" rx="10" fill="#CCFBF1"/>
            <path d="M226 360v-72a34 34 0 0 1 68 0v72Z" fill="url(#dh-door)" opacity="0.92"/>
            <circle cx="282" cy="326" r="3.5" fill="#FFFFFF"/>
            <path d="M64 404c60-6 112-20 152-40" fill="none" stroke="#14B8A6" stroke-width="4" stroke-linecap="round" stroke-dasharray="2 12"/>
            <g transform="translate(40 330)">
                <path d="M20 0a20 20 0 0 1 20 20c0 16-20 38-20 38S0 36 0 20A20 20 0 0 1 20 0Z" fill="#16A34A"/>
                <circle cx="20" cy="20" r="7" fill="#FFFFFF"/>
            </g>
            <g filter="url(#dh-soft-2)"><rect x="372" y="120" width="112" height="104" rx="20" fill="#FFFFFF"/></g>
            <g transform="translate(372 120)">
                <rect x="18" y="18" width="76" height="10" rx="5" fill="#16A34A" opacity="0.25"/>
                <rect x="18" y="42" width="18" height="18" rx="6" fill="#F3FAF6"/>
                <rect x="47" y="42" width="18" height="18" rx="6" fill="#F3FAF6"/>
                <rect x="76" y="42" width="18" height="18" rx="6" fill="#F3FAF6"/>
                <rect x="18" y="70" width="18" height="18" rx="6" fill="#F3FAF6"/>
                <rect x="47" y="70" width="18" height="18" rx="6" fill="#16A34A"/>
                <rect x="76" y="70" width="18" height="18" rx="6" fill="#F3FAF6"/>
                <path d="M51 79l4 4 7-8" stroke="#FFFFFF" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>
            <path d="M408 360c-4-30 4-56 22-74-2 26-8 52-22 74Z" fill="#16A34A" opacity="0.7"/>
            <path d="M412 360c12-22 30-34 50-36-10 18-28 32-50 36Z" fill="#14B8A6" opacity="0.55"/>
        </svg>
    @endif
</div>
