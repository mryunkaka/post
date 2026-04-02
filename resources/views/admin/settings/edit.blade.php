<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Admin Panel</p>
            <h2 class="mt-2 text-2xl font-semibold leading-tight text-stone-900">
                Setting Situs
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
                    <div class="space-y-6">
                        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">General</h3>

                            <div class="mt-5 space-y-5">
                                <div>
                                    <x-input-label for="site_name" value="Site Name" />
                                    <x-text-input id="site_name" name="site_name" type="text" class="mt-2 block w-full"
                                        :value="old('site_name', $settings['site_name'])" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('site_name')" />
                                </div>

                                <div>
                                    <x-input-label for="site_tagline" value="Tagline" />
                                    <x-text-input id="site_tagline" name="site_tagline" type="text" class="mt-2 block w-full"
                                        :value="old('site_tagline', $settings['site_tagline'])" />
                                    <x-input-error class="mt-2" :messages="$errors->get('site_tagline')" />
                                </div>

                                <div>
                                    <x-input-label for="contact_email" value="Contact Email" />
                                    <x-text-input id="contact_email" name="contact_email" type="email" class="mt-2 block w-full"
                                        :value="old('contact_email', $settings['contact_email'])" />
                                    <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
                                </div>

                                <div>
                                    <x-input-label for="site_description" value="Site Description" />
                                    <textarea id="site_description" name="site_description" rows="4"
                                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('site_description', $settings['site_description']) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('site_description')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Feature Flags</h3>

                            <div class="mt-5 space-y-4">
                                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                                    <input type="hidden" name="feature_amp_enabled" value="0">
                                    <input id="feature_amp_enabled" name="feature_amp_enabled" type="checkbox" value="1"
                                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                                        @checked(old('feature_amp_enabled', $settings['feature_amp_enabled']))>
                                    <span>Aktifkan AMP</span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                                    <input type="hidden" name="feature_ai_enabled" value="0">
                                    <input id="feature_ai_enabled" name="feature_ai_enabled" type="checkbox" value="1"
                                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                                        @checked(old('feature_ai_enabled', $settings['feature_ai_enabled']))>
                                    <span>Aktifkan AI Editorial</span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                                    <input type="hidden" name="feature_comment_enabled" value="0">
                                    <input id="feature_comment_enabled" name="feature_comment_enabled" type="checkbox" value="1"
                                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                                        @checked(old('feature_comment_enabled', $settings['feature_comment_enabled']))>
                                    <span>Aktifkan komentar publik</span>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-stone-200 bg-stone-950 p-6 text-stone-100 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-300">Persisted</p>
                            <p class="mt-4 text-sm leading-7 text-stone-300">
                                Setting disimpan di tabel `settings` agar operasional tidak bergantung pada edit file config.
                            </p>

                            <div class="mt-6">
                                <x-primary-button>Simpan Setting</x-primary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
