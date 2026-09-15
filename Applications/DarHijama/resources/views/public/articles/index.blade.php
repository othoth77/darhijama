@extends('dar-hijama::public.layout')

@section('content')
    <nav class="mx-auto max-w-6xl px-6 pt-6 text-xs text-gray-500" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1">
            <li><a href="{{ route('dar-hijama.public.home') }}" class="hover:text-dar-hijama-green">الرئيسية</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-dar-hijama-ink" aria-current="page">
                {{ $activeCategory ? $activeCategory->name : 'المقالات' }}
            </li>
        </ol>
    </nav>

    <header class="mx-auto max-w-6xl px-6 pt-6">
        <h1 class="text-3xl font-extrabold text-dar-hijama-ink sm:text-4xl">
            {{ $activeCategory ? $activeCategory->name : 'مقالات دار الحجامة' }}
        </h1>
        @if ($activeCategory?->description)
            <p class="mt-3 max-w-2xl text-gray-600">{{ $activeCategory->description }}</p>
        @endif
    </header>

    @if ($categories->isNotEmpty())
        <div class="mx-auto max-w-6xl px-6 pt-6">
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('dar-hijama.articles.index') }}"
                    class="rounded-full border px-3 py-1 text-xs font-semibold {{ $activeCategory ? 'border-gray-300 text-gray-600 hover:border-dar-hijama-green hover:text-dar-hijama-green' : 'border-dar-hijama-green bg-dar-hijama-green text-white' }}"
                >الكل</a>
                @foreach ($categories as $category)
                    <a
                        href="{{ route('dar-hijama.articles.category', $category) }}"
                        class="rounded-full border px-3 py-1 text-xs font-semibold {{ $activeCategory?->is($category) ? 'border-dar-hijama-green bg-dar-hijama-green text-white' : 'border-gray-300 text-gray-600 hover:border-dar-hijama-green hover:text-dar-hijama-green' }}"
                    >{{ $category->name }} ({{ $category->articles_count }})</a>
                @endforeach
            </div>
        </div>
    @endif

    <section class="mx-auto max-w-6xl px-6 py-10">
        @if ($articles->isEmpty())
            <p class="rounded-xl border border-gray-200 bg-gray-50 p-8 text-center text-gray-500">
                لا توجد مقالات منشورة بعد في هذا القسم.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <article class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white">
                        @if ($article->featured_image)
                            <a href="{{ route('dar-hijama.articles.show', $article) }}">
                                <img
                                    src="{{ asset('storage/'.$article->featured_image) }}"
                                    alt="{{ $article->featured_image_alt ?: $article->title }}"
                                    width="400" height="220"
                                    loading="lazy"
                                    class="h-44 w-full object-cover"
                                >
                            </a>
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            @if ($article->category)
                                <span class="mb-2 text-xs font-bold uppercase tracking-wide text-dar-hijama-turquoise">
                                    {{ $article->category->name }}
                                </span>
                            @endif
                            <h2 class="font-bold text-dar-hijama-ink">
                                <a href="{{ route('dar-hijama.articles.show', $article) }}" class="hover:text-dar-hijama-green">
                                    {{ $article->title }}
                                </a>
                            </h2>
                            @if ($article->excerpt)
                                <p class="mt-2 flex-1 text-sm leading-6 text-gray-600">{{ $article->excerpt }}</p>
                            @endif
                            <time datetime="{{ $article->published_at?->toDateString() }}" class="mt-4 text-xs text-gray-400">
                                {{ $article->published_at?->locale('ar')->translatedFormat('j F Y') }}
                            </time>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $articles->links() }}
            </div>
        @endif
    </section>
@endsection
