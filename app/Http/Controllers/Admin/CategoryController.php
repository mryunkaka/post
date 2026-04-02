<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\FrontCacheService;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class CategoryController extends Controller
{
    public function __construct(
        protected SlugService $slugService,
        protected FrontCacheService $frontCacheService,
    ) {}

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->withCount('articles')
                ->with('parent')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category([
                'sort_order' => 0,
                'is_active' => true,
            ]),
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = new Category;
        $category->fill($this->payload($request->validated(), $category));
        $category->save();
        $this->frontCacheService->flushCategoryRelatedCaches();

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => $this->parentOptions($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->fill($this->payload($request->validated(), $category));
        $category->save();
        $this->frontCacheService->flushCategoryRelatedCaches();

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $category->delete();
            $this->frontCacheService->flushCategoryRelatedCaches();
        } catch (QueryException) {
            return redirect()
                ->route('admin.categories.index')
                ->with('status', 'Kategori tidak dapat dihapus karena masih dipakai artikel atau relasi lain.');
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payload(array $data, Category $category): array
    {
        $name = trim((string) $data['name']);
        $slugSource = trim((string) ($data['slug'] ?: $name));

        return [
            'parent_id' => Arr::get($data, 'parent_id') ?: null,
            'name' => $name,
            'slug' => $this->slugService->generateUniqueSlug($slugSource, Category::class, 'slug', $category),
            'description' => $this->nullableString($data, 'description'),
            'sort_order' => (int) Arr::get($data, 'sort_order', 0),
            'is_active' => (bool) Arr::get($data, 'is_active', false),
            'seo_title' => $this->nullableString($data, 'seo_title'),
            'seo_description' => $this->nullableString($data, 'seo_description'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function nullableString(array $data, string $key): ?string
    {
        $value = Arr::get($data, $key);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function parentOptions(?Category $ignore = null)
    {
        return Category::query()
            ->when($ignore !== null, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
