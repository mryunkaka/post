<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CategoryProvisionService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function resolveOrCreate(array $data): Category
    {
        $name = $this->normalizeName((string) Arr::get($data, 'name', ''));
        $slugSource = trim((string) Arr::get($data, 'slug', ''));
        $slugCandidate = $slugSource !== '' ? Str::slug($slugSource) : Str::slug($name);

        $existingCategory = Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->orWhere('slug', $slugCandidate)
            ->first();

        if ($existingCategory !== null) {
            return $existingCategory;
        }

        $category = new Category;
        $category->fill([
            'parent_id' => null,
            'name' => $name,
            'slug' => $this->slugService->generateUniqueSlug($slugSource !== '' ? $slugSource : $name, Category::class),
            'description' => $this->nullableString($data, 'description'),
            'sort_order' => (int) Arr::get($data, 'sort_order', 999),
            'is_active' => (bool) Arr::get($data, 'is_active', true),
            'seo_title' => $this->nullableString($data, 'seo_title') ?? $name,
            'seo_description' => $this->nullableString($data, 'seo_description') ?? $this->nullableString($data, 'description'),
        ]);
        $category->save();

        return $category;
    }

    protected function normalizeName(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->squish()
            ->title()
            ->value();
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
}
