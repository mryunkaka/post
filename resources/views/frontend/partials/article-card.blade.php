@php
    /** @var \App\Models\Article $article */
    $compact = $compact ?? false;
@endphp

<article class="overflow-hidden rounded-[2rem] border border-stone-200/80 bg-white shadow-[0_24px_80px_-48px_rgba(28,25,23,0.45)]">
    @if ($article->featuredImageUrl())
        <a href="{{ $article->publicUrl() }}" class="block">
            <img
                src="{{ $article->featuredImageUrl() }}"
                alt="{{ $article->title }}"
                class="{{ $compact ? 'h-44' : 'h-56' }} w-full object-cover"
                loading="lazy"
            >
        </a>
    @else
        <a href="{{ $article->publicUrl() }}" class="flex {{ $compact ? 'h-44' : 'h-56' }} items-end bg-[linear-gradient(135deg,_#0f172a,_#1d4ed8,_#f59e0b)] p-6 text-white">
            <span class="max-w-xs text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                {{ $article->category?->name ?? 'Berita' }}
            </span>
        </a>
    @endif

    <div class="space-y-4 p-6">
        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">
            @if ($article->category)
                <a href="{{ $article->category->publicUrl() }}" class="rounded-full bg-amber-100 px-3 py-1 text-amber-900 transition hover:bg-amber-200">
                    {{ $article->category->name }}
                </a>
            @endif

            @if ($article->published_at)
                <span>{{ $article->published_at->translatedFormat('d M Y') }}</span>
            @endif
        </div>

        <div class="space-y-3">
            <h3 class="{{ $compact ? 'text-xl' : 'text-2xl' }} font-semibold leading-tight text-stone-950">
                <a href="{{ $article->publicUrl() }}" class="transition hover:text-cyan-700">
                    {{ $article->title }}
                </a>
            </h3>

            @if ($article->excerpt)
                <p class="text-sm leading-7 text-stone-600">
                    {{ $article->excerpt }}
                </p>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-stone-500">
            <span>{{ $article->author?->name ?? 'Redaksi' }}</span>
            <a href="{{ $article->publicUrl() }}" class="font-semibold text-stone-900 transition hover:text-cyan-700">
                Baca selengkapnya
            </a>
        </div>
    </div>
</article>
