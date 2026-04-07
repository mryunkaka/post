@if ($paginator->hasPages())
    <div class="flex flex-col gap-3 rounded-3xl border border-stone-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-stone-600">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </p>

        <div class="flex items-center gap-2">
            <span class="px-2 text-sm text-stone-500">
                {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
            </span>

            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-200 text-stone-300">
                    @include('admin.partials.icon', ['name' => 'chevron-left'])
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-300 text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                    @include('admin.partials.icon', ['name' => 'chevron-left'])
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-300 text-stone-700 transition hover:border-stone-900 hover:text-stone-900">
                    @include('admin.partials.icon', ['name' => 'chevron-right'])
                </a>
            @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-200 text-stone-300">
                    @include('admin.partials.icon', ['name' => 'chevron-right'])
                </span>
            @endif
        </div>
    </div>
@endif
