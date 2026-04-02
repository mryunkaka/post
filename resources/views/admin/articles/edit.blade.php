<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Edit Artikel</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    {{ $article->title }}
                </h2>
            </div>
            <a href="{{ route('admin.articles.show', $article) }}"
                class="inline-flex items-center rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                Preview Data
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.articles.update', $article) }}" class="space-y-6">
                @method('PATCH')
                @include('admin.articles._form')
            </form>

            <form id="submit-review-form" method="POST" action="{{ route('admin.articles.submit-review', $article) }}">
                @csrf
                @method('PATCH')
            </form>

            <form id="publish-form" method="POST" action="{{ route('admin.articles.publish', $article) }}">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </div>
</x-app-layout>
