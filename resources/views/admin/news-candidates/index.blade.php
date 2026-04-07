<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">AI Newsroom</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">Review Kandidat Berita</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-3">
                @foreach (['' => 'Semua', 'pending' => 'Pending', 'validated' => 'Validated', 'rejected' => 'Rejected', 'drafted' => 'Drafted'] as $value => $label)
                    <a
                        href="{{ route('admin.news-candidates.index', array_filter(['status' => $value, 'region' => $currentRegion])) }}"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $currentStatus === $value ? 'border-stone-950 bg-stone-950 text-white' : 'border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.news-candidates.index', array_filter(['status' => $currentStatus])) }}"
                    class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $currentRegion === '' ? 'border-amber-600 bg-amber-600 text-white' : 'border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900' }}"
                >
                    Semua Wilayah
                </a>
                @foreach ($availableRegions as $region)
                    <a
                        href="{{ route('admin.news-candidates.index', array_filter(['status' => $currentStatus, 'region' => $region])) }}"
                        class="rounded-full border px-4 py-2 text-sm font-semibold capitalize transition {{ $currentRegion === $region ? 'border-amber-600 bg-amber-600 text-white' : 'border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900' }}"
                    >
                        {{ str_replace('-', ' ', $region) }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                            <tr>
                                <th class="px-6 py-4">Kandidat</th>
                                <th class="px-6 py-4">Sumber</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Artikel</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 bg-white">
                            @forelse ($candidates as $candidate)
                                <tr class="align-top">
                                    <td class="px-6 py-5">
                                        <p class="font-semibold text-stone-900">{{ $candidate->title }}</p>
                                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-500">
                                            {{ str_replace('-', ' ', $candidate->region ?: 'tanpa-wilayah') }}
                                            @if ($candidate->source_published_at)
                                                • {{ $candidate->source_published_at->format('d M Y H:i') }}
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
                                    <td class="px-6 py-5">
                                        <div class="flex min-w-[220px] flex-wrap justify-end gap-2">
                                            @if ($candidate->status === 'validated' && ! $candidate->article)
                                                <form method="POST" action="{{ route('admin.news-candidates.generate-draft', $candidate) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-full border border-sky-300 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:border-sky-700 hover:text-sky-900">
                                                        Buat Draft
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.news-candidates.validate', $candidate) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-full border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-700 hover:text-emerald-900">
                                                    Validate
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.news-candidates.reject', $candidate) }}" class="flex flex-wrap justify-end gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="reason" value="{{ $candidate->rejection_reason }}" placeholder="Alasan reject" class="w-40 rounded-full border border-stone-300 px-3 py-1.5 text-xs text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0" />
                                                <button type="submit" class="rounded-full border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-700 hover:text-rose-900">
                                                    Reject
                                                </button>
                                            </form>
                                            @if ($candidate->status !== 'pending')
                                                <form method="POST" action="{{ route('admin.news-candidates.reset', $candidate) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-full border border-stone-300 px-3 py-1.5 text-xs font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                                                        Reset
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-14 text-center text-stone-500">
                                        Belum ada kandidat berita AI untuk direview.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $candidates->links() }}
        </div>
    </div>
</x-app-layout>
