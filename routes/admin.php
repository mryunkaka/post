<?php

use App\Http\Controllers\Admin\ArticleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,editor,wartawan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::resource('articles', ArticleController::class)->except('destroy');
        Route::patch('articles/{article}/submit-review', [ArticleController::class, 'submitForReview'])
            ->name('articles.submit-review');
        Route::patch('articles/{article}/publish', [ArticleController::class, 'publish'])
            ->middleware('role:admin,editor')
            ->name('articles.publish');
    });
