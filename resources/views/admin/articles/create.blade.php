<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Artikel Baru</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    Tulis Draft Redaksi
                </h2>
            </div>

            <button
                type="button"
                data-clear-local-draft="admin-article-create"
                class="inline-flex items-center rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-rose-500 hover:text-rose-700"
            >
                Clear Draft
            </button>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.articles.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
                data-local-draft="admin-article-create"
            >
                @include('admin.articles._form')
            </form>
        </div>
    </div>
</x-app-layout>
