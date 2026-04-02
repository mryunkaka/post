<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Admin Panel</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    Manajemen Artikel
                </h2>
            </div>
            <a href="{{ route('admin.articles.create') }}"
                class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold transition"
                style="background-color: #1c1917; color: #ffffff; border: 1px solid #1c1917;">
                Tulis Artikel
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

            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                            <tr>
                                <th class="px-6 py-4">Artikel</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Author</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Update</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 bg-white">
                            @forelse ($articles as $article)
                                <tr class="align-top">
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-stone-900">{{ $article->title }}</p>
                                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-500">{{ $article->slug }}</p>
                                        @if ($article->tags->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($article->tags as $tag)
                                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-stone-700">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-stone-700">{{ $article->category->name }}</td>
                                    <td class="px-6 py-5 text-stone-700">{{ $article->author->name }}</td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                                            {{ $article->status }}
                                        </span>
                                        @if ($article->archived_at)
                                            <div class="mt-2">
                                                <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-900">
                                                    archived
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-stone-600">
                                        {{ $article->updated_at?->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.articles.show', $article) }}"
                                                class="text-sm font-semibold text-stone-500 transition hover:text-stone-900">
                                                Lihat
                                            </a>
                                            <a href="{{ route('admin.articles.edit', $article) }}"
                                                class="text-sm font-semibold text-amber-700 transition hover:text-amber-900">
                                                Edit
                                            </a>
                                            @if (auth()->user()->role === 'admin' || (auth()->user()->role === 'editor' && in_array($article->status, ['draft', 'review'], true)))
                                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                                                    onsubmit="return confirm('Hapus artikel ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm font-semibold text-rose-700 transition hover:text-rose-900">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-14 text-center text-stone-500">
                                        Belum ada artikel. Mulai dari draft pertama redaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $articles->links() }}
        </div>
    </div>
</x-app-layout>
