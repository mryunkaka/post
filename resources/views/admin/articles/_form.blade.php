@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="space-y-5">
                <div>
                    <x-input-label for="title" value="Judul" />
                    <x-text-input id="title" name="title" type="text" class="mt-2 block w-full"
                        :value="old('title', $article->title)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-input-label for="slug" value="Slug" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-2 block w-full"
                        :value="old('slug', $article->slug)" />
                    <p class="mt-2 text-xs text-stone-500">Kosongkan bila ingin auto-generate dari judul.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                </div>

                <div>
                    <x-input-label for="excerpt" value="Excerpt" />
                    <textarea id="excerpt" name="excerpt" rows="4"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('excerpt', $article->excerpt) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('excerpt')" />
                </div>

                <div>
                    <x-input-label for="tags" value="Tags" />
                    <x-text-input id="tags" name="tags" type="text" class="mt-2 block w-full"
                        :value="old('tags', $article->tags->pluck('name')->implode(', '))" />
                    <p class="mt-2 text-xs text-stone-500">Pisahkan tag dengan koma. Contoh: Ekonomi, Pelabuhan, Kotabaru</p>
                    <x-input-error class="mt-2" :messages="$errors->get('tags')" />
                </div>

                <div>
                    <x-input-label for="content" value="Konten" />
                    <input id="content" type="hidden" name="content" value="{{ old('content', $article->content) }}">
                    <div data-rich-editor data-input="content" class="mt-2 block w-full"></div>
                    <p class="mt-2 text-xs text-stone-500">Editor artikel memakai format HTML ringan. Upload media akan ditambahkan lewat modul media terpisah.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('content')" />
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Pengaturan</h3>

            <div class="mt-5 space-y-5">
                <div>
                    <x-input-label for="featured_image" value="Featured Image" />
                    @if ($article->featuredImageUrl())
                        <div class="mt-2 overflow-hidden rounded-3xl border border-stone-200 bg-stone-50">
                            <img src="{{ $article->featuredImageUrl() }}" alt="{{ $article->title }}"
                                class="h-48 w-full object-cover">
                        </div>
                    @endif
                    <input id="featured_image" name="featured_image" type="file" accept=".jpg,.jpeg,.png,.webp"
                        class="mt-2 block w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm text-stone-700 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-stone-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-stone-700">
                    <p class="mt-2 text-xs text-stone-500">Format: JPG, PNG, atau WebP. Maksimal 2MB. Gambar akan diubah ke WebP dan diperkecil otomatis.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('featured_image')" />
                </div>

                @if ($article->featured_image)
                    <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                        <input type="hidden" name="remove_featured_image" value="0">
                        <input id="remove_featured_image" name="remove_featured_image" type="checkbox" value="1"
                            class="mt-1 rounded border-stone-300 text-rose-600 shadow-sm focus:ring-rose-500"
                            @checked(old('remove_featured_image'))>
                        <span>Hapus featured image saat artikel disimpan.</span>
                    </label>
                    <x-input-error class="mt-2" :messages="$errors->get('remove_featured_image')" />
                @else
                    <input type="hidden" name="remove_featured_image" value="0">
                @endif
            </div>

            <div class="mt-5 space-y-5">
                <div>
                    <x-input-label for="category_id" value="Kategori" />
                    <select id="category_id" name="category_id"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected((string) old('category_id', $article->category_id) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                </div>

                <div>
                    <x-input-label for="schema_type" value="Schema Type" />
                    <x-text-input id="schema_type" name="schema_type" type="text" class="mt-2 block w-full"
                        :value="old('schema_type', $article->schema_type ?: 'NewsArticle')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('schema_type')" />
                </div>

                <div>
                    <x-input-label for="meta_title" value="Meta Title" />
                    <x-text-input id="meta_title" name="meta_title" type="text" class="mt-2 block w-full"
                        :value="old('meta_title', $article->meta_title)" />
                    <x-input-error class="mt-2" :messages="$errors->get('meta_title')" />
                </div>

                <div>
                    <x-input-label for="meta_description" value="Meta Description" />
                    <textarea id="meta_description" name="meta_description" rows="4"
                        class="mt-2 block w-full rounded-2xl border-stone-300 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('meta_description', $article->meta_description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('meta_description')" />
                </div>

                <label class="flex items-start gap-3 rounded-2xl border border-stone-200 p-4 text-sm text-stone-700">
                    <input type="hidden" name="is_featured" value="0">
                    <input id="is_featured" name="is_featured" type="checkbox" value="1"
                        class="mt-1 rounded border-stone-300 text-amber-600 shadow-sm focus:ring-amber-500"
                        @checked(old('is_featured', $article->is_featured))>
                    <span>
                        Tandai sebagai featured article.
                    </span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('is_featured')" />
            </div>
        </div>

        <div class="rounded-3xl border border-stone-200 bg-stone-950 p-6 text-stone-100 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-300">Workflow</p>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Status</dt>
                    <dd class="rounded-full bg-white/10 px-3 py-1 font-semibold uppercase tracking-wide">
                        {{ $article->status ?: 'draft' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Author</dt>
                    <dd>{{ $article->author?->name ?? auth()->user()->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-stone-400">Publish</dt>
                    <dd>{{ $article->published_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-primary-button>Simpan Draft</x-primary-button>

                @if ($article->exists && $article->status !== 'published')
                    <button type="submit" form="submit-review-form"
                        class="inline-flex items-center rounded-full border border-amber-400/40 px-4 py-2 text-sm font-semibold text-amber-200 transition hover:border-amber-300 hover:text-white">
                        Submit Review
                    </button>
                @endif

                @if ($article->exists && in_array(auth()->user()->role, ['admin', 'editor'], true) && $article->status !== 'published')
                    <button type="submit" form="publish-form"
                        class="inline-flex items-center rounded-full bg-amber-400 px-4 py-2 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">
                        Publish
                    </button>
                @endif
            </div>

            @if ($article->review_notes)
                <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-stone-200">
                    <p class="font-semibold text-white">Review Notes</p>
                    <p class="mt-2 whitespace-pre-line">{{ $article->review_notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
