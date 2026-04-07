<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Admin Panel</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">Moderasi Komentar</h2>
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

            <form method="GET" action="{{ route('admin.comments.index') }}" class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1.4fr)_180px_180px_180px_auto]">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Cari</span>
                        <input
                            type="text"
                            name="q"
                            value="{{ $searchQuery }}"
                            placeholder="Nama, email, isi, IP, judul artikel"
                            class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0"
                        />
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Status</span>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Semua Status</option>
                            @foreach (['pending', 'approved', 'rejected', 'spam'] as $status)
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
                        <a href="{{ route('admin.comments.index') }}" class="inline-flex h-12 items-center rounded-full border border-stone-300 px-5 text-sm font-semibold text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div data-bulk-table class="space-y-4">
                <form id="comments-bulk-form" data-bulk-form method="POST" action="{{ route('admin.comments.bulk') }}">
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
                        <select form="comments-bulk-form" name="action" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm text-stone-700 focus:border-stone-900 focus:outline-none focus:ring-0">
                            <option value="">Pilih bulk action</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                            <option value="spam">Spam</option>
                            <option value="delete">Hapus</option>
                        </select>
                        <button form="comments-bulk-form" type="submit" class="inline-flex h-12 items-center rounded-full border border-amber-600 bg-amber-600 px-5 text-sm font-semibold text-white transition hover:bg-amber-500">
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
                                    <th class="px-6 py-4">Komentar</th>
                                    <th class="px-6 py-4">Artikel</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Meta</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-white">
                                @forelse ($comments as $comment)
                                    <tr class="align-top">
                                        <td class="px-6 py-5">
                                            <input form="comments-bulk-form" type="checkbox" name="selected_ids[]" value="{{ $comment->id }}" data-row-checkbox class="mt-1 h-4 w-4 rounded border-stone-300 text-stone-900 focus:ring-stone-900" />
                                        </td>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-stone-900">{{ $comment->displayName() }}</p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-500">{{ $comment->guest_email ?? $comment->author?->email ?? '-' }}</p>
                                            @if ($comment->parent)
                                                <p class="mt-3 rounded-2xl bg-stone-100 px-4 py-3 text-xs text-stone-600">
                                                    Reply ke: {{ \Illuminate\Support\Str::limit($comment->parent->content, 90) }}
                                                </p>
                                            @endif
                                            <p class="mt-3 whitespace-pre-line text-stone-700">{{ $comment->content }}</p>
                                        </td>
                                        <td class="px-6 py-5">
                                            <a href="{{ route('articles.show', $comment->article->slug) }}" class="font-semibold text-amber-700 transition hover:text-amber-900" target="_blank" rel="noreferrer">
                                                {{ $comment->article->title }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                                                {{ $comment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-stone-600">
                                            <p>{{ $comment->created_at?->format('d M Y H:i') }}</p>
                                            <p class="mt-2 text-xs">{{ $comment->ip_address ?: '-' }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex min-w-[196px] flex-nowrap items-center justify-end gap-2 whitespace-nowrap">
                                                <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-emerald-300 text-emerald-700 hover:border-emerald-700 hover:text-emerald-900" title="Approve">
                                                        @include('admin.partials.icon', ['name' => 'check'])
                                                        <span class="sr-only">Approve</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-amber-300 text-amber-700 hover:border-amber-700 hover:text-amber-900" title="Reject">
                                                        @include('admin.partials.icon', ['name' => 'x-mark'])
                                                        <span class="sr-only">Reject</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.comments.spam', $comment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-rose-300 text-rose-700 hover:border-rose-700 hover:text-rose-900" title="Spam">
                                                        @include('admin.partials.icon', ['name' => 'flag'])
                                                        <span class="sr-only">Spam</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="{{ $actionButtonBase }} border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900" title="Hapus">
                                                        @include('admin.partials.icon', ['name' => 'trash'])
                                                        <span class="sr-only">Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center text-stone-500">
                                            Belum ada komentar pada filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @include('admin.partials.pagination', ['paginator' => $comments])
        </div>
    </div>
</x-app-layout>
