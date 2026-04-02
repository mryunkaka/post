<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * @return Collection<int, string>
     */
    public function normalizeTags(?string $input): Collection
    {
        if ($input === null) {
            return collect();
        }

        return collect(explode(',', $input))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->map(fn (string $tag) => Str::title(mb_strtolower($tag)))
            ->unique()
            ->values();
    }

    public function syncArticleTags(Article $article, ?string $input): void
    {
        $tags = $this->normalizeTags($input);

        if ($tags->isEmpty()) {
            $article->tags()->sync([]);

            return;
        }

        $tagIds = $tags->map(function (string $name): int {
            $existing = Tag::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

            if ($existing !== null) {
                return $existing->id;
            }

            $tag = Tag::query()->create([
                'name' => $name,
                'slug' => $this->slugService->generateUniqueSlug($name, Tag::class, 'slug'),
            ]);

            return $tag->id;
        })->all();

        $article->tags()->sync($tagIds);
    }
}
