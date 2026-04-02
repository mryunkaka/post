@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="space-y-5">
                <div>
                    <x-input-label for="name" value="Nama Kategori" />
                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full"
                        :value="old('name', $category->name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="slug" value="Slug" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-2 block w-full"
                        :value="old('slug', $category->slug)" />
                    <p class="mt-2 text-xs text-stone-500">Kosongkan bila ingin auto-generate dari nama kategori.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                </div>

                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea id="description" name="description" rows="5"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description', $category->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div>
                    <x-input-label for="seo_title" value="SEO Title" />
                    <x-text-input id="seo_title" name="seo_title" type="text" class="mt-2 block w-full"
                        :value="old('seo_title', $category->seo_title)" />
                    <x-input-error class="mt-2" :messages="$errors->get('seo_title')" />
                </div>

                <div>
                    <x-input-label for="seo_description" value="SEO Description" />
                    <textarea id="seo_description" name="seo_description" rows="4"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('seo_description', $category->seo_description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Pengaturan</h3>

            <div class="mt-5 space-y-5">
                <div>
                    <x-input-label for="parent_id" value="Parent Category" />
                    <select id="parent_id" name="parent_id"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Tanpa parent</option>
                        @foreach ($parentOptions as $parentCategory)
                            <option value="{{ $parentCategory->id }}"
                                @selected((string) old('parent_id', $category->parent_id) === (string) $parentCategory->id)>
                                {{ $parentCategory->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                </div>

                <div>
                    <x-input-label for="sort_order" value="Urutan" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-2 block w-full"
                        :value="old('sort_order', $category->sort_order ?? 0)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                </div>

                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1"
                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                        @checked(old('is_active', $category->is_active ?? true))>
                    <span>Aktifkan kategori untuk pemakaian editorial.</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-stone-950 p-6 text-stone-100 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-300">Kategori</p>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Status</dt>
                    <dd class="rounded-full bg-white/10 px-3 py-1 font-semibold uppercase tracking-wide">
                        {{ ($category->is_active ?? true) ? 'active' : 'inactive' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Artikel</dt>
                    <dd>{{ $category->articles_count ?? 0 }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <x-primary-button>Simpan Kategori</x-primary-button>
            </div>
        </div>
    </div>
</div>
