<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SettingController;
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
        Route::patch('articles/{article}/archive', [ArticleController::class, 'archive'])
            ->middleware('role:admin,editor')
            ->name('articles.archive');
        Route::patch('articles/{article}/restore', [ArticleController::class, 'restore'])
            ->middleware('role:admin,editor')
            ->name('articles.restore');

        Route::middleware('role:admin,editor')->group(function (): void {
            Route::resource('categories', CategoryController::class)->except('show');
        });

        Route::middleware('role:admin')->group(function (): void {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
