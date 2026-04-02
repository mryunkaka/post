@extends('frontend.layouts.app')

@section('content')
    <section class="grid gap-8 lg:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
        <div class="space-y-6">
            <div class="space-y-4">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-cyan-700">Headline Hari Ini</p>
                <h1 class="max-w-4xl text-4xl font-semibold leading-tight text-stone-950 md:text-5xl">
                    {{ $headline?->title ?? 'Portal berita lokal dengan workflow redaksi yang rapi dan cepat.' }}
                </h1>
                <p class="max-w-3xl text-lg leading-8 text-stone-600">
                    {{ $headline?->excerpt ?: ($siteDescription ?: 'Ikuti perkembangan berita lokal, ekonomi, dan isu publik terbaru dari redaksi digital yang terstruktur.') }}
                </p>
            </div>

            @if ($headline)
                <article class="overflow-hidden rounded-[2.25rem] border border-stone-200/80 bg-white shadow-[0_36px_120px_-72px_rgba(28,25,23,0.7)]">
                    @if ($headline->featuredImageUrl())
                        <a href="{{ $headline->publicUrl() }}" class="block">
                            <img
                                src="{{ $headline->featuredImageUrl() }}"
                                alt="{{ $headline->title }}"
                                class="h-[22rem] w-full object-cover"
                            >
                        </a>
                    @else
                        <a href="{{ $headline->publicUrl() }}" class="flex h-[22rem] items-end bg-[linear-gradient(135deg,_#0f172a,_#164e63,_#f59e0b)] p-8 text-white">
                            <span class="text-sm font-semibold uppercase tracking-[0.35em] text-white/75">
                                {{ $headline->category?->name ?? 'Berita Utama' }}
                            </span>
                        </a>
                    @endif

                    <div class="space-y-5 p-8">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
                            @if ($headline->category)
                                <a href="{{ $headline->category->publicUrl() }}" class="rounded-full bg-cyan-100 px-3 py-1 text-cyan-900 transition hover:bg-cyan-200">
                                    {{ $headline->category->name }}
                                </a>
                            @endif

                            <span>{{ $headline->published_at?->translatedFormat('d M Y H:i') }}</span>
                            <span>{{ $headline->author?->name ?? 'Redaksi' }}</span>
                        </div>

                        <h2 class="text-3xl font-semibold leading-tight text-stone-950">
                            <a href="{{ $headline->publicUrl() }}" class="transition hover:text-cyan-700">
                                {{ $headline->title }}
                            </a>
                        </h2>

                        <p class="max-w-3xl text-base leading-8 text-stone-600">{{ $headline->excerpt }}</p>

                        @if ($headline->tags->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($headline->tags as $tag)
                                    <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-medium text-stone-600">
                                        #{{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @else
                <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-10 text-stone-500">
                    Belum ada artikel published yang siap tampil di homepage.
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-stone-200/80 bg-stone-950 p-6 text-white shadow-[0_24px_80px_-48px_rgba(15,23,42,0.9)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300">Pencarian Cepat</p>
                <h2 class="mt-3 text-2xl font-semibold">Temukan berita sesuai topik.</h2>
                <p class="mt-3 text-sm leading-7 text-white/70">
                    Cari liputan berdasarkan judul, ringkasan, atau isi artikel terbaru.
                </p>

                <form action="{{ route('search.index') }}" method="GET" class="mt-6 space-y-3">
                    <input
                        type="search"
                        name="q"
                        placeholder="Contoh: pelabuhan kotabaru"
                        class="w-full rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/45 focus:border-cyan-300 focus:outline-none"
                    >
                    <button type="submit" class="w-full rounded-2xl bg-amber-400 px-4 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-stone-950 transition hover:bg-amber-300">
                        Cari Berita
                    </button>
                </form>
            </section>

            <section class="rounded-[2rem] border border-stone-200/80 bg-white p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Populer</p>
                        <h2 class="mt-2 text-2xl font-semibold text-stone-950">Berita banyak dibaca</h2>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($popularArticles as $popularArticle)
                        <article class="rounded-[1.5rem] border border-stone-200/80 p-4 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
                                {{ $popularArticle->category?->name ?? 'Berita' }} | {{ number_format($popularArticle->views_count) }} views
                            </p>
                            <h3 class="mt-2 text-lg font-semibold leading-tight text-stone-950">
                                <a href="{{ $popularArticle->publicUrl() }}" class="transition hover:text-cyan-700">
                                    {{ $popularArticle->title }}
                                </a>
                            </h3>
                        </article>
                    @empty
                        <p class="text-sm text-stone-500">Belum ada artikel populer.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </section>

    <section class="mt-12 space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Terbaru</p>
                <h2 class="mt-2 text-3xl font-semibold text-stone-950">Update terbaru dari redaksi</h2>
            </div>

            <p class="max-w-2xl text-sm leading-7 text-stone-600">
                Halaman depan menampilkan artikel published terbaru dengan pemisahan headline, daftar berita segar, dan sorotan kategori utama.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($latestArticles as $article)
                @include('frontend.partials.article-card', ['article' => $article])
            @empty
                <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-8 text-stone-500 md:col-span-2 xl:col-span-3">
                    Belum ada artikel terbaru lain selain headline.
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-12 rounded-[2.5rem] border border-stone-200/80 bg-white px-6 py-8 shadow-[0_24px_80px_-48px_rgba(28,25,23,0.45)] lg:px-8">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-700">Kategori Utama</p>
                <h2 class="mt-2 text-3xl font-semibold text-stone-950">Jelajahi berita per kanal</h2>
            </div>
            <p class="max-w-2xl text-sm leading-7 text-stone-600">
                Kanal aktif diprioritaskan sesuai urutan redaksi dan jumlah artikel published yang sudah tersedia.
            </p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($mainCategories as $mainCategory)
                <a href="{{ $mainCategory->publicUrl() }}" class="rounded-[1.75rem] border border-stone-200 bg-stone-50 p-5 transition hover:-translate-y-1 hover:border-cyan-300 hover:bg-cyan-50/50">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Kategori</p>
                    <h3 class="mt-3 text-2xl font-semibold text-stone-950">{{ $mainCategory->name }}</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-600">
                        {{ $mainCategory->description ?: 'Kumpulan berita published dalam kanal ini.' }}
                    </p>
                    <p class="mt-5 text-sm font-semibold text-cyan-700">
                        {{ number_format($mainCategory->published_articles_count) }} artikel aktif
                    </p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
