<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Edit Kategori</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    {{ $category->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-6">
                @method('PATCH')
                @include('admin.categories._form')
            </form>
        </div>
    </div>
</x-app-layout>
