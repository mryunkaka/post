@extends('frontend.layouts.app')

@section('content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(300px,0.8fr)]">
        <section class="space-y-8">
            <header class="space-y-4 rounded-[2rem] border border-stone-200/80 bg-white p-8 shadow-[0_24px_80px_-48px_rgba(28,25,23,0.45)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-700">Kategori</p>
                <h1 class="text-4xl font-semibold text-stone-950">{{ $category->name }}</h1>
                <p class="max-w-3xl text-base leading-8 text-stone-600">
                    {{ $category->description ?: 'Daftar artikel published di kanal ini.' }}
                </p>
            </header>

            <div class="space-y-6">
                @forelse ($articles as $article)
                    @include('frontend.partials.article-card', ['article' => $article])
                @empty
                    <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-8 text-stone-500">
                        Belum ada artikel published di kategori ini.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $articles->links() }}
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-stone-200/80 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Navigasi</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($frontCategories as $navCategory)
                        <a
                            href="{{ $navCategory->publicUrl() }}"
                            class="rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-[0.24em] transition {{ $navCategory->id === $category->id ? 'border-cyan-700 bg-cyan-700 text-white' : 'border-stone-300 text-stone-600 hover:border-cyan-300 hover:text-stone-950' }}"
                        >
                            {{ $navCategory->name }}
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[2rem] border border-stone-200/80 bg-stone-950 p-6 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">Populer di Kanal Ini</p>
                <div class="mt-6 space-y-4">
                    @forelse ($popularArticles as $popularArticle)
                        <article class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/55">
                                {{ number_format($popularArticle->views_count) }} views
                            </p>
                            <h3 class="mt-2 text-lg font-semibold leading-tight">
                                <a href="{{ $popularArticle->publicUrl() }}" class="transition hover:text-cyan-300">
                                    {{ $popularArticle->title }}
                                </a>
                            </h3>
                        </article>
                    @empty
                        <p class="text-sm text-white/65">Belum ada artikel populer di kategori ini.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection
