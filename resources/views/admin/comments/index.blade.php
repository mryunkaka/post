<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Admin Panel</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">Moderasi Komentar</h2>
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
                @foreach (['' => 'Semua', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'spam' => 'Spam'] as $value => $label)
                    <a
                        href="{{ $value === '' ? route('admin.comments.index') : route('admin.comments.index', ['status' => $value]) }}"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $currentStatus === $value ? 'border-stone-950 bg-stone-950 text-white' : 'border-stone-300 text-stone-700 hover:border-stone-900 hover:text-stone-900' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                            <tr>
                                <th class="px-6 py-4">Komentar</th>
                                <th class="px-6 py-4">Artikel</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Meta</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 bg-white">
                            @forelse ($comments as $comment)
                                <tr class="align-top">
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
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-full border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-700 hover:text-emerald-900">
                                                    Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-full border border-amber-300 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:border-amber-700 hover:text-amber-900">
                                                    Reject
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.comments.spam', $comment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-full border border-rose-300 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-700 hover:text-rose-900">
                                                    Spam
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-14 text-center text-stone-500">
                                        Belum ada komentar untuk dimoderasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $comments->links() }}
        </div>
    </div>
</x-app-layout>
