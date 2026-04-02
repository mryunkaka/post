<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,editor,wartawan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::resource('articles', ArticleController::class);
        Route::patch('articles/{article}/submit-review', [ArticleController::class, 'submitForReview'])
            ->name('articles.submit-review');
        Route::patch('articles/{article}/publish', [ArticleController::class, 'publish'])
            ->middleware('role:admin,editor')
            ->name('articles.publish');

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::resource('categories', CategoryController::class)->except('show');
        });
    });
