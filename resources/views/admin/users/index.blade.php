<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Admin Panel</p>
                <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                    Manajemen User
                </h2>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold transition"
                style="background-color: #1c1917; color: #ffffff; border: 1px solid #1c1917;">
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                            <tr>
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Artikel</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Last Login</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 bg-white">
                            @forelse ($users as $user)
                                <tr class="align-top">
                                    <td class="px-6 py-5 font-semibold text-stone-900">{{ $user->name }}</td>
                                    <td class="px-6 py-5 text-stone-600">{{ $user->email }}</td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-stone-600">{{ $user->articles_count }}</td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-700' }}">
                                            {{ $user->is_active ? 'active' : 'inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-stone-600">{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="text-sm font-semibold text-amber-700 transition hover:text-amber-900">
                                                Edit
                                            </a>
                                            @if (! auth()->user()->is($user))
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    onsubmit="return confirm('Hapus user ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-sm font-semibold text-rose-700 transition hover:text-rose-900">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center text-stone-500">
                                        Belum ada user internal tambahan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
