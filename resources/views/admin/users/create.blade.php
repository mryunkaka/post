<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">User Baru</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                Tambah User Internal
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @include('admin.users._form')
            </form>
        </div>
    </div>
</x-app-layout>
