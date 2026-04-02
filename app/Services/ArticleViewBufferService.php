<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class ArticleViewBufferService
{
    public const VIEW_BUFFER_TTL = 86400;

    public function __construct(
        protected FrontCacheService $frontCacheService,
    ) {}

    public function record(Article $article): void
    {
        $registryKey = $this->registryKey();
        $viewKey = $this->viewKey($article->id);

        $trackedArticleIds = Cache::get($registryKey, []);

        if (! in_array($article->id, $trackedArticleIds, true)) {
            $trackedArticleIds[] = $article->id;
            Cache::put($registryKey, array_values(array_unique($trackedArticleIds)), self::VIEW_BUFFER_TTL);
        }

        Cache::add($viewKey, 0, self::VIEW_BUFFER_TTL);
        Cache::increment($viewKey);
    }

    public function flush(): int
    {
        $trackedArticleIds = Cache::pull($this->registryKey(), []);
        $flushedRows = 0;

        foreach ($trackedArticleIds as $articleId) {
            $views = (int) Cache::pull($this->viewKey((int) $articleId), 0);

            if ($views <= 0) {
                continue;
            }

            $flushedRows += Article::query()
                ->whereKey((int) $articleId)
                ->increment('views_count', $views);
        }

        if ($flushedRows > 0) {
            $this->frontCacheService->forgetPopularArticles();
            $this->frontCacheService->forgetHomepage();
        }

        return $flushedRows;
    }

    protected function registryKey(): string
    {
        return $this->frontCacheService->key('articles.views.buffer.registry');
    }

    protected function viewKey(int $articleId): string
    {
        return $this->frontCacheService->key('articles.views.buffer.'.$articleId);
    }
}
