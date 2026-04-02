<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Redaksi2
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6 text-gray-900">
                        <p class="text-sm font-medium uppercase tracking-[0.24em] text-amber-600">Session Auth Active</p>
                        <h3 class="mt-3 text-2xl font-semibold text-stone-900">
                            Selamat datang, {{ auth()->user()->name }}
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-stone-600">
                            Anda berhasil masuk ke baseline panel internal
                            {{ config('app.brand_name', config('app.name')) }}.
                            Tahap berikutnya adalah middleware role, route admin terpisah, dan CRUD artikel.
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden bg-stone-950 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-stone-100">
                        <p class="text-sm font-medium uppercase tracking-[0.24em] text-amber-400">Profil Akses</p>
                        <dl class="mt-4 space-y-4 text-sm">
                            <div>
                                <dt class="text-stone-400">Nama</dt>
                                <dd class="mt-1 font-semibold text-white">{{ auth()->user()->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-stone-400">Email</dt>
                                <dd class="mt-1 text-stone-200">{{ auth()->user()->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-stone-400">Role</dt>
                                <dd
                                    class="mt-1 inline-flex rounded-full bg-amber-400/15 px-3 py-1 font-semibold uppercase tracking-wide text-amber-300">
                                    {{ auth()->user()->role }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
