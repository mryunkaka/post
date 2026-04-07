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

    @php
        $actionButtonBase = 'inline-flex h-9 w-9 items-center justify-center rounded-full border transition';
        $canDeleteArticles = auth()->user()->role === 'admin' || auth()->user()->role === 'editor';
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('admin.articles.index') }}" class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_180px_180px_180px_auto]">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Cari</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $searchQuery }}"
                            placeholder="Judul, slug, kategori, tag, sumber"
                            class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0"
                        />
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Status</span>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" @selected($currentStatus === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Dari Tanggal</span>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0" />
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Sampai Tanggal</span>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0" />
                    </label>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex h-12 items-center rounded-full border border-stone-950 bg-stone-950 px-5 text-sm font-semibold text-white transition hover:bg-stone-800">
                            Filter
                        </button>
                        <a href="{{ route('admin.articles.index') }}" class="inline-flex h-12 items-center rounded-full border border-stone-300 px-5 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div data-bulk-table class="space-y-4">
                <form id="articles-bulk-form" data-bulk-form method="POST" action="{{ route('admin.articles.bulk') }}">
                @csrf
                <input type="hidden" name="selection_scope" value="page" />
                <input type="hidden" name="status" value="{{ $currentStatus }}" />
                <input type="hidden" name="q" value="{{ $searchQuery }}" />
                <input type="hidden" name="date_from" value="{{ $dateFrom }}" />
                <input type="hidden" name="date_to" value="{{ $dateTo }}" />
                </form>

                <div class="flex flex-col gap-4 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-select-scope="page" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">Pilih halaman</button>
                        <button type="button" data-select-scope="filtered" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">Pilih hasil filter</button>
                        <button type="button" data-select-scope="all" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">Pilih semua data</button>
                        <button type="button" data-select-scope="clear" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">Batal pilih</button>
                        <span data-selected-count class="rounded-full bg-stone-100 px-4 py-2 text-sm font-semibold text-stone-700">0 dipilih</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <select form="articles-bulk-form" name="action" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Pilih bulk action</option>
                            <option value="submit_review">Kirim ke review</option>
                            @if (auth()->user()->role !== 'wartawan')
                                <option value="publish">Publish / jadwalkan</option>
                                <option value="archive">Arsipkan</option>
                                <option value="restore">Pulihkan arsip</option>
                            @endif
                            @if ($canDeleteArticles)
                                <option value="delete">Hapus</option>
                            @endif
                        </select>
                        <button form="articles-bulk-form" type="submit" class="inline-flex h-12 items-center rounded-full border border-amber-600 bg-amber-600 px-5 text-sm font-semibold text-white transition hover:bg-amber-500">
                            Terapkan
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200 text-sm">
                            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                                <tr>
                                    <th class="px-6 py-4">
                                        <input type="checkbox" data-toggle-page class="h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-stone-900" />
                                    </th>
                                    <th class="px-6 py-4">Artikel</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4">Author</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Update</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-white">
                                @forelse ($articles as $article)
                                    <tr class="align-top">
                                        <td class="px-6 py-5">
                                            <input form="articles-bulk-form" type="checkbox" name="selected_ids[]" value="{{ $article->id }}" data-row-checkbox class="mt-1 h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-stone-900" />
                                        </td>
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
                                            <div class="flex min-w-[148px] flex-nowrap items-center justify-end gap-2 whitespace-nowrap">
                                                <a
                                                    href="{{ route('admin.articles.show', $article) }}"
                                                    class="{{ $actionButtonBase }} border-stone-300 text-stone-600 hover:border-stone-900 hover:text-stone-900"
                                                    title="Lihat"
                                                >
                                                    @include('admin.partials.icon', ['name' => 'eye'])
                                                    <span class="sr-only">Lihat</span>
                                                </a>

                                                <a
                                                    href="{{ route('admin.articles.edit', $article) }}"
                                                    class="{{ $actionButtonBase }} border-amber-300 text-amber-700 hover:border-amber-700 hover:text-amber-900"
                                                    title="Edit"
                                                >
                                                    @include('admin.partials.icon', ['name' => 'pencil-square'])
                                                    <span class="sr-only">Edit</span>
                                                </a>

                                                @if (auth()->user()->role === 'admin' || (auth()->user()->role === 'editor' && in_array($article->status, ['draft', 'review'], true)))
                                                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="{{ $actionButtonBase }} border-rose-300 text-rose-700 hover:border-rose-700 hover:text-rose-900"
                                                            title="Hapus"
                                                        >
                                                            @include('admin.partials.icon', ['name' => 'trash'])
                                                            <span class="sr-only">Hapus</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-14 text-center text-stone-500">
                                            Belum ada artikel pada filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('admin.partials.pagination', ['paginator' => $articles])
        </div>
    </div>
</x-app-layout>
