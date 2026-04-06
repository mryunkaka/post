<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: static function (): void {
            RateLimiter::for('public', static function (Request $request): Limit {
                return Limit::perMinute(60)->by($request->ip());
            });

            RateLimiter::for('auth-login', static function (Request $request): Limit {
                return Limit::perMinute(10)->by($request->ip());
            });

            RateLimiter::for('api', static function (Request $request): Limit {
                $key = $request->user()?->getAuthIdentifier() ?: $request->ip();

                return Limit::perMinute(30)->by((string) $key);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
