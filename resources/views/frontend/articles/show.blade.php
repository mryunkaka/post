@extends('frontend.layouts.app')

@section('content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(300px,0.8fr)]">
        <article class="space-y-8">
            <nav class="flex flex-wrap items-center gap-2 text-sm text-stone-500">
                <a href="{{ route('home') }}" class="transition hover:text-stone-950">Homepage</a>
                <span>/</span>
                @if ($article->category)
                    <a href="{{ $article->category->publicUrl() }}" class="transition hover:text-stone-950">{{ $article->category->name }}</a>
                    <span>/</span>
                @endif
                <span class="text-stone-700">{{ $article->title }}</span>
            </nav>

            <header class="space-y-5">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
                    @if ($article->category)
                        <a href="{{ $article->category->publicUrl() }}" class="rounded-full bg-amber-100 px-3 py-1 text-amber-900 transition hover:bg-amber-200">
                            {{ $article->category->name }}
                        </a>
                    @endif

                    <span>{{ $article->published_at?->translatedFormat('d M Y H:i') }}</span>
                    <span>{{ $article->author?->name ?? 'Redaksi' }}</span>
                </div>

                <h1 class="max-w-4xl text-4xl font-semibold leading-tight text-stone-950 md:text-5xl">
                    {{ $article->title }}
                </h1>

                @if ($article->excerpt)
                    <p class="max-w-3xl text-lg leading-8 text-stone-600">
                        {{ $article->excerpt }}
                    </p>
                @endif
            </header>

            @if ($article->featuredImageUrl())
                <div class="overflow-hidden rounded-[2rem] border border-stone-200/80 bg-white shadow-[0_28px_90px_-54px_rgba(28,25,23,0.55)]">
                    <img
                        src="{{ $article->featuredImageUrl() }}"
                        alt="{{ $article->title }}"
                        class="h-auto w-full object-cover"
                    >
                </div>
            @endif

            @if ($article->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($article->tags as $tag)
                        <span class="rounded-full border border-stone-300 bg-white px-3 py-1 text-xs font-medium text-stone-600">
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="article-content rounded-[2rem] border border-stone-200/80 bg-white p-8 shadow-[0_28px_90px_-54px_rgba(28,25,23,0.4)]">
                {!! $article->content !!}
            </div>

            <section class="space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Artikel Terkait</p>
                        <h2 class="mt-2 text-3xl font-semibold text-stone-950">Baca lanjutan topik serupa</h2>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    @forelse ($relatedArticles as $relatedArticle)
                        @include('frontend.partials.article-card', ['article' => $relatedArticle, 'compact' => true])
                    @empty
                        <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-8 text-stone-500 md:col-span-2">
                            Belum ada artikel terkait lain di kategori ini.
                        </div>
                    @endforelse
                </div>
            </section>
        </article>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-stone-200/80 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Ringkasan</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-stone-900">Kategori</dt>
                        <dd class="mt-1 text-stone-600">{{ $article->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Penulis</dt>
                        <dd class="mt-1 text-stone-600">{{ $article->author?->name ?? 'Redaksi' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Slug</dt>
                        <dd class="mt-1 break-all text-stone-600">{{ $article->slug }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Views</dt>
                        <dd class="mt-1 text-stone-600">{{ number_format($article->views_count) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-[2rem] border border-stone-200/80 bg-stone-950 p-6 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">Populer</p>
                <h2 class="mt-2 text-2xl font-semibold">Berita lain yang sedang dicari</h2>

                <div class="mt-6 space-y-4">
                    @forelse ($popularArticles as $popularArticle)
                        <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/55">
                                {{ $popularArticle->category?->name ?? 'Berita' }}
                            </p>
                            <h3 class="mt-2 text-lg font-semibold leading-tight">
                                <a href="{{ $popularArticle->publicUrl() }}" class="transition hover:text-cyan-300">
                                    {{ $popularArticle->title }}
                                </a>
                            </h3>
                        </article>
                    @empty
                        <p class="text-sm text-white/65">Belum ada artikel populer.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection
