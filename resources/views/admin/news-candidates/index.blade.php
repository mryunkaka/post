<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">AI Newsroom</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">Review Kandidat Berita</h2>
        </div>
    </x-slot>

    @php
        $actionButtonBase = 'inline-flex h-9 w-9 items-center justify-center rounded-full border transition';
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('admin.news-candidates.index') }}" class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.3fr)_180px_200px_180px_180px_auto]">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Cari</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $searchQuery }}"
                            placeholder="Judul, ringkasan, sumber, wilayah"
                            class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0"
                        />
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Status</span>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Semua Status</option>
                            @foreach (['pending', 'validated', 'rejected', 'drafted'] as $status)
                                <option value="{{ $status }}" @selected($currentStatus === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Wilayah</span>
                        <select name="region" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm capitalize text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Semua Wilayah</option>
                            @foreach ($availableRegions as $region)
                                <option value="{{ $region }}" @selected($currentRegion === $region)>{{ str_replace('-', ' ', $region) }}</option>
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

                    <div class="flex items-end gap-2 xl:col-start-6">
                        <button type="submit" class="inline-flex h-12 items-center rounded-full border border-stone-950 bg-stone-950 px-5 text-sm font-semibold text-white transition hover:bg-stone-800">
                            Filter
                        </button>
                        <a href="{{ route('admin.news-candidates.index') }}" class="inline-flex h-12 items-center rounded-full border border-stone-300 px-5 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div data-bulk-table class="space-y-4">
                <form id="news-candidates-bulk-form" data-bulk-form method="POST" action="{{ route('admin.news-candidates.bulk') }}">
                @csrf
                <input type="hidden" name="selection_scope" value="page" />
                <input type="hidden" name="status" value="{{ $currentStatus }}" />
                <input type="hidden" name="region" value="{{ $currentRegion }}" />
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
                        <select form="news-candidates-bulk-form" name="action" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Pilih bulk action</option>
                            <option value="validate">Validate</option>
                            <option value="reject">Reject</option>
                            <option value="reset">Reset</option>
                            <option value="delete">Hapus</option>
                        </select>
                        <button form="news-candidates-bulk-form" type="submit" class="inline-flex h-12 items-center rounded-full border border-amber-600 bg-amber-600 px-5 text-sm font-semibold text-white transition hover:bg-amber-500">
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
                                    <th class="px-6 py-4">Kandidat</th>
                                    <th class="px-6 py-4">Sumber</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Artikel</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-white">
                                @forelse ($candidates as $candidate)
                                    <tr class="align-top">
                                        <td class="px-6 py-5">
                                            <input form="news-candidates-bulk-form" type="checkbox" name="selected_ids[]" value="{{ $candidate->id }}" data-row-checkbox class="mt-1 h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-stone-900" />
                                        </td>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-stone-900">{{ $candidate->title }}</p>
                                            <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-500">
                                                {{ str_replace('-', ' ', $candidate->region ?: 'tanpa wilayah') }}
                                                @if ($candidate->source_published_at)
                                                    &bull; {{ $candidate->source_published_at->format('d M Y H:i') }}
                                                @endif
                                            </p>
                                            @if ($candidate->excerpt)
                                                <p class="mt-3 text-stone-700">{{ $candidate->excerpt }}</p>
                                            @endif
                                            @if ($candidate->facts_summary)
                                                <p class="mt-3 rounded-2xl bg-stone-100 px-4 py-3 text-xs leading-6 text-stone-600">
                                                    {{ $candidate->facts_summary }}
                                                </p>
                                            @endif
                                            @if ($candidate->rejection_reason)
                                                <p class="mt-3 rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-6 text-rose-700">
                                                    {{ $candidate->rejection_reason }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-stone-700">
                                            <p class="font-semibold text-stone-900">{{ $candidate->source_name }}</p>
                                            <a href="{{ $candidate->source_url }}" target="_blank" rel="noreferrer" class="mt-2 inline-block break-all text-amber-700 transition hover:text-amber-900">
                                                {{ $candidate->source_url }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $candidate->status === 'validated' ? 'bg-emerald-100 text-emerald-900' : ($candidate->status === 'rejected' ? 'bg-rose-100 text-rose-900' : ($candidate->status === 'drafted' ? 'bg-sky-100 text-sky-900' : 'bg-amber-100 text-amber-900')) }}">
                                                {{ $candidate->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-stone-700">
                                            @if ($candidate->article)
                                                <a href="{{ route('admin.articles.edit', $candidate->article) }}" class="font-semibold text-amber-700 transition hover:text-amber-900">
                                                    {{ $candidate->article->title }}
                                                </a>
                                            @else
                                                <span class="text-stone-400">Belum ada draft</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex min-w-[236px] flex-nowrap items-center justify-end gap-2 whitespace-nowrap">
                                                @if ($candidate->status === 'validated' && ! $candidate->article)
                                                    <form method="POST" action="{{ route('admin.news-candidates.generate-draft', $candidate) }}">
                                                        @csrf
                                                        <button type="submit" class="{{ $actionButtonBase }} border-sky-300 text-sky-700 hover:border-sky-700 hover:text-sky-900" title="Buat draft">
                                                            @include('admin.partials.icon', ['name' => 'sparkles'])
                                                            <span class="sr-only">Buat Draft</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('admin.news-candidates.validate', $candidate) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-emerald-300 text-emerald-700 hover:border-emerald-700 hover:text-emerald-900" title="Validate">
                                                        @include('admin.partials.icon', ['name' => 'check'])
                                                        <span class="sr-only">Validate</span>
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.news-candidates.reject', $candidate) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-amber-300 text-amber-700 hover:border-amber-700 hover:text-amber-900" title="Reject">
                                                        @include('admin.partials.icon', ['name' => 'x-mark'])
                                                        <span class="sr-only">Reject</span>
                                                    </button>
                                                </form>

                                                @if ($candidate->status !== 'pending')
                                                    <form method="POST" action="{{ route('admin.news-candidates.reset', $candidate) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="{{ $actionButtonBase }} border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900" title="Reset">
                                                            @include('admin.partials.icon', ['name' => 'arrow-path'])
                                                            <span class="sr-only">Reset</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if (! $candidate->article_id)
                                                    <form method="POST" action="{{ route('admin.news-candidates.destroy', $candidate) }}" onsubmit="return confirm('Hapus kandidat AI ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="{{ $actionButtonBase }} border-rose-300 text-rose-700 hover:border-rose-700 hover:text-rose-900" title="Hapus">
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
                                        <td colspan="6" class="px-6 py-14 text-center text-stone-500">
                                            Belum ada kandidat berita AI pada filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('admin.partials.pagination', ['paginator' => $candidates])
        </div>
    </div>
</x-app-layout>
