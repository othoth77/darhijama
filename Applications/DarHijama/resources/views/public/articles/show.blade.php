@extends('dar-hijama::public.layout')

@section('content')
    <article>
        <nav class="mx-auto max-w-3xl px-6 pt-6 text-xs text-gray-500" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('dar-hijama.public.home') }}" class="hover:text-dar-hijama-green">الرئيسية</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('dar-hijama.articles.index') }}" class="hover:text-dar-hijama-green">المقالات</a></li>
                @if ($article->category)
                    <li aria-hidden="true">/</li>
                    <li>
                        <a href="{{ route('dar-hijama.articles.category', $article->category) }}" class="hover:text-dar-hijama-green">
                            {{ $article->category->name }}
                        </a>
                    </li>
                @endif
                <li aria-hidden="true">/</li>
                <li class="text-dar-hijama-ink" aria-current="page">
                    {{ $article->breadcrumb_label ?: $article->title }}
                </li>
            </ol>
        </nav>

        <header class="mx-auto max-w-3xl px-6 pt-6">
            <h1 class="text-3xl font-extrabold leading-tight text-dar-hijama-ink sm:text-4xl">
                {{ $article->title }}
            </h1>
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                <span>{{ $article->author?->name ?? 'دار الحجامة' }}</span>
                <time datetime="{{ $article->published_at?->toIso8601String() }}">
                    نُشر في {{ $article->published_at?->locale('ar')->translatedFormat('j F Y') }}
                </time>
                @if ($article->updated_at && $article->published_at && $article->updated_at->gt($article->published_at->addHour()))
                    <time datetime="{{ $article->updated_at->toIso8601String() }}">
                        · آخر تحديث {{ $article->updated_at->locale('ar')->translatedFormat('j F Y') }}
                    </time>
                @endif
            </div>
        </header>

        @if ($article->featured_image)
            <img
                src="{{ asset('storage/'.$article->featured_image) }}"
                alt="{{ $article->featured_image_alt ?: $article->title }}"
                width="768" height="420"
                class="mx-auto mt-6 max-w-3xl rounded-xl object-cover px-6"
            >
        @endif

        <div class="prose-dar-hijama mx-auto max-w-3xl px-6 py-8 leading-8 text-gray-700 [&_a]:text-dar-hijama-green [&_a]:underline [&_blockquote]:border-e-4 [&_blockquote]:border-dar-hijama-turquoise [&_blockquote]:ps-4 [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:text-dar-hijama-ink [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-dar-hijama-ink [&_img]:rounded-lg [&_li]:mb-1 [&_ol]:list-decimal [&_ol]:ps-6 [&_p]:mb-4 [&_ul]:list-disc [&_ul]:ps-6">
            {!! $article->purifiedContent() !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="mx-auto max-w-3xl px-6 pb-6">
                <div class="flex flex-wrap gap-2">
                    @foreach ($article->tags as $tag)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mx-auto max-w-3xl px-6 pb-10">
            <div class="dh-cta-bg flex flex-col items-start gap-5 rounded-3xl border border-dar-hijama-green/15 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <p class="text-lg font-bold text-dar-hijama-ink">هل لديك سؤال أو تريد حجز موعد؟</p>
                <a
                    href="{{ $whatsappBookingUrl }}"
                    target="_blank" rel="noopener"
                    x-data="whatsappCta('article')" @click="track()"
                    class="dh-btn dh-btn-primary"
                >احجز موعدك</a>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-gray-100 bg-gray-50/60">
            <div class="mx-auto max-w-6xl px-6 py-12">
                <h2 class="text-xl font-extrabold text-dar-hijama-ink">مقالات ذات صلة</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-3">
                    @foreach ($related as $relatedArticle)
                        <a
                            href="{{ route('dar-hijama.articles.show', $relatedArticle) }}"
                            class="block rounded-xl border border-gray-200 bg-white p-5 hover:border-dar-hijama-green"
                        >
                            <span class="text-xs font-semibold text-dar-hijama-teal-deep">
                                {{ $relatedArticle->category?->name }}
                            </span>
                            <h3 class="mt-1 font-bold text-dar-hijama-ink">{{ $relatedArticle->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
