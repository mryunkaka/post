@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="space-y-5">
                <div>
                    <x-input-label for="name" value="Nama" />
                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full"
                        :value="old('name', $user->name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-2 block w-full"
                        :value="old('email', $user->email)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="password" :value="$user->exists ? 'Password Baru' : 'Password'" />
                        <x-text-input id="password" name="password" type="password" class="mt-2 block w-full"
                            autocomplete="new-password" />
                        @if ($user->exists)
                            <p class="mt-2 text-xs text-stone-500">Kosongkan jika password tidak ingin diubah.</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-2 block w-full" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Akses</h3>

            <div class="mt-5 space-y-5">
                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        required>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        <option value="editor" @selected(old('role', $user->role) === 'editor')>Editor</option>
                        <option value="wartawan" @selected(old('role', $user->role) === 'wartawan')>Wartawan</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                </div>

                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1"
                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                        @checked(old('is_active', $user->is_active ?? true))>
                    <span>Aktifkan user agar bisa login ke panel internal.</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-stone-950 p-6 text-stone-100 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-300">User</p>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Status</dt>
                    <dd class="rounded-full bg-white/10 px-3 py-1 font-semibold uppercase tracking-wide">
                        {{ ($user->is_active ?? true) ? 'active' : 'inactive' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Role</dt>
                    <dd>{{ old('role', $user->role ?: 'wartawan') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Artikel</dt>
                    <dd>{{ $user->articles_count ?? 0 }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Last Login</dt>
                    <dd>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <x-primary-button>Simpan User</x-primary-button>
            </div>
        </div>
    </div>
</div>
