<?php

namespace App\Providers;

use App\Services\FrontCacheService;
use App\Services\SettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('frontend.*', function ($view): void {
            static $sharedData = null;

            if ($sharedData === null) {
                $settingService = app(SettingService::class);

                $sharedData = [
                    'siteName' => $settingService->get('site_name', config('app.name')),
                    'siteDescription' => $settingService->get('site_description', ''),
                    'siteTagline' => $settingService->get('site_tagline', ''),
                    'frontCategories' => app(FrontCacheService::class)->rememberActiveCategories(),
                ];
            }

            $view->with($sharedData);
        });
    }
}
