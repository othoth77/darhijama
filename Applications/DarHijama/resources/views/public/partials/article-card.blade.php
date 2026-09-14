@php($articleUrl = route('dar-hijama.articles.show', $article))
<article class="dh-reveal group flex flex-col">
    <a href="{{ $articleUrl }}" tabindex="-1" aria-hidden="true" class="block overflow-hidden rounded-2xl bg-dar-hijama-mist">
        @if ($article->featured_image)
            <img
                src="{{ asset('storage/'.$article->featured_image) }}"
                alt="{{ $article->featured_image_alt ?: $article->title }}"
                width="640" height="400"
                loading="lazy" decoding="async"
                class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            >
        @else
            <div class="grid aspect-[16/10] w-full place-items-center bg-gradient-to-br from-dar-hijama-mint via-white to-teal-50">
                <img src="{{ asset('images/brand/dar-hijama-piste1-icone.svg') }}" alt="" width="56" height="56" loading="lazy" class="h-14 w-14 opacity-80 transition duration-500 group-hover:scale-105">
            </div>
        @endif
    </a>

    <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
        @if ($article->category)
            <span class="font-bold text-dar-hijama-teal-deep">{{ $article->category->name }}</span>
            <span class="h-1 w-1 rounded-full bg-gray-300" aria-hidden="true"></span>
        @endif
        <time datetime="{{ $article->published_at?->toDateString() }}" class="text-gray-500">{{ $article->published_at?->locale('ar')->translatedFormat('j F Y') }}</time>
    </div>

    <h3 class="mt-3 text-xl font-bold leading-snug text-dar-hijama-ink">
        <a href="{{ $articleUrl }}" class="dh-focus rounded transition hover:text-dar-hijama-green-deep">{{ $article->title }}</a>
    </h3>

    @if ($article->excerpt)
        <p class="mt-3 line-clamp-3 leading-7 text-gray-600">{{ $article->excerpt }}</p>
    @endif

    <a href="{{ $articleUrl }}" class="dh-focus mt-5 inline-flex w-fit items-center gap-2 rounded font-bold text-dar-hijama-green-deep transition-all hover:gap-3">
        اقرأ المقال<span class="sr-only">: {{ $article->title }}</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
    </a>
</article>
