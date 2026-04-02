<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Ringkasan Artikel</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    {{ $article->title }}
                </h2>
            </div>
            <a href="{{ route('admin.articles.edit', $article) }}"
                class="inline-flex items-center rounded-full bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-700">
                Edit Artikel
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (auth()->user()->role === 'admin' || (auth()->user()->role === 'editor' && in_array($article->status, ['draft', 'review'], true)))
                <div class="flex justify-end">
                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                        onsubmit="return confirm('Hapus artikel ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center rounded-full border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-700 hover:text-rose-900">
                            Hapus Artikel
                        </button>
                    </form>
                </div>
            @endif

            <div class="rounded-3xl border border-stone-200 bg-white p-8 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                        {{ $article->status }}
                    </span>
                    <span class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-stone-700">
                        {{ $article->category->name }}
                    </span>
                </div>

                <dl class="mt-6 grid gap-5 text-sm text-stone-600 sm:grid-cols-2">
                    <div>
                        <dt class="font-semibold text-stone-900">Slug</dt>
                        <dd class="mt-1">{{ $article->slug }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Author</dt>
                        <dd class="mt-1">{{ $article->author->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Published At</dt>
                        <dd class="mt-1">{{ $article->published_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-900">Schema Type</dt>
                        <dd class="mt-1">{{ $article->schema_type }}</dd>
                    </div>
                </dl>

                @if ($article->excerpt)
                    <div class="mt-8 rounded-2xl bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-500">Excerpt</p>
                        <p class="mt-3 text-sm leading-7 text-stone-700">{{ $article->excerpt }}</p>
                    </div>
                @endif

                <div class="prose prose-stone mt-8 max-w-none whitespace-pre-line text-sm leading-7">
                    {{ $article->content }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
