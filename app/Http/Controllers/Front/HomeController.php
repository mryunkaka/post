<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\FrontCacheService;

class HomeController extends Controller
{
    public function __construct(
        protected FrontCacheService $frontCacheService,
    ) {}

    public function __invoke()
    {
        $payload = $this->frontCacheService->rememberHomepagePayload();

        return view('frontend.home', [
            'headline' => $payload['headline'],
            'latestArticles' => $payload['latestArticles'],
            'popularArticles' => $payload['popularArticles'],
            'mainCategories' => $payload['mainCategories'],
            'metaTitle' => null,
            'metaDescription' => null,
        ]);
    }
}
