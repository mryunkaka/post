@extends('frontend.layouts.app')

@section('content')
    <section class="space-y-8">
        <header class="rounded-[2rem] border border-stone-200/80 bg-white p-8 shadow-[0_24px_80px_-48px_rgba(28,25,23,0.45)]">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-700">Pencarian</p>
            <h1 class="mt-3 text-4xl font-semibold text-stone-950">Cari berita publik</h1>
            <p class="mt-3 max-w-3xl text-base leading-8 text-stone-600">
                Temukan artikel published berdasarkan judul, ringkasan, atau isi konten.
            </p>

            <form action="{{ route('search.index') }}" method="GET" class="mt-6 flex flex-col gap-3 sm:flex-row">
                <input
                    type="search"
                    name="q"
                    value="{{ $keyword }}"
                    placeholder="Masukkan kata kunci berita"
                    class="w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-cyan-500"
                >
                <button type="submit" class="rounded-2xl bg-stone-950 px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-stone-800">
                    Cari
                </button>
            </form>
        </header>

        @if ($keyword === '')
            <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-8 text-stone-500">
                Masukkan kata kunci untuk mulai mencari berita.
            </div>
        @else
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Hasil</p>
                    <h2 class="mt-2 text-3xl font-semibold text-stone-950">
                        {{ number_format($articles->total()) }} artikel untuk "{{ $keyword }}"
                    </h2>
                </div>
                <p class="max-w-2xl text-sm leading-7 text-stone-600">
                    Hasil dibatasi ke artikel published yang belum diarsipkan, dengan pagination 15 item per halaman.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($articles as $article)
                    @include('frontend.partials.article-card', ['article' => $article])
                @empty
                    <div class="rounded-[2rem] border border-dashed border-stone-300 bg-white/70 p-8 text-stone-500 md:col-span-2 xl:col-span-3">
                        Tidak ada artikel yang cocok dengan kata kunci ini.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $articles->links() }}
            </div>
        @endif
    </section>
@endsection
