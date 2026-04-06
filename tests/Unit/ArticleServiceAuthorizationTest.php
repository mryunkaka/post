<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use App\Services\EditorialMailService;
use App\Services\FrontCacheService;
use App\Services\MediaService;
use App\Services\SlugService;
use App\Services\TagService;
use Illuminate\Auth\Access\AuthorizationException;
use PHPUnit\Framework\TestCase;

class ArticleServiceAuthorizationTest extends TestCase
{
    public function test_wartawan_can_view_owned_article_even_if_user_id_is_string(): void
    {
        $service = new ArticleService(
            new SlugService,
            $this->createMock(MediaService::class),
            $this->createMock(TagService::class),
            $this->createMock(FrontCacheService::class),
            $this->createMock(EditorialMailService::class),
        );
        $actor = new User;
        $actor->id = 7;
        $actor->role = 'wartawan';

        $article = new Article;
        $article->user_id = '7';

        $service->assertCanView($actor, $article);

        $this->assertTrue(true);
    }

    public function test_wartawan_cannot_view_other_users_article(): void
    {
        $service = new ArticleService(
            new SlugService,
            $this->createMock(MediaService::class),
            $this->createMock(TagService::class),
            $this->createMock(FrontCacheService::class),
            $this->createMock(EditorialMailService::class),
        );
        $actor = new User;
        $actor->id = 7;
        $actor->role = 'wartawan';

        $article = new Article;
        $article->user_id = '9';

        $this->expectException(AuthorizationException::class);

        $service->assertCanView($actor, $article);
    }
}
