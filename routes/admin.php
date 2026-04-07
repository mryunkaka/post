<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\NewsCandidateController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,editor,wartawan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::resource('articles', ArticleController::class);
        Route::post('articles/bulk', [ArticleController::class, 'bulk'])->name('articles.bulk');
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
            Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
            Route::post('comments/bulk', [CommentController::class, 'bulk'])->name('comments.bulk');
            Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::patch('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
            Route::patch('comments/{comment}/spam', [CommentController::class, 'spam'])->name('comments.spam');
            Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
            Route::get('news-candidates', [NewsCandidateController::class, 'index'])->name('news-candidates.index');
            Route::post('news-candidates/bulk', [NewsCandidateController::class, 'bulk'])->name('news-candidates.bulk');
            Route::patch('news-candidates/{newsCandidate}/validate', [NewsCandidateController::class, 'validateCandidate'])->name('news-candidates.validate');
            Route::patch('news-candidates/{newsCandidate}/reject', [NewsCandidateController::class, 'reject'])->name('news-candidates.reject');
            Route::patch('news-candidates/{newsCandidate}/reset', [NewsCandidateController::class, 'reset'])->name('news-candidates.reset');
            Route::post('news-candidates/{newsCandidate}/generate-draft', [NewsCandidateController::class, 'generateDraft'])->name('news-candidates.generate-draft');
            Route::delete('news-candidates/{newsCandidate}', [NewsCandidateController::class, 'destroy'])->name('news-candidates.destroy');
        });

        Route::middleware('role:admin')->group(function (): void {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::resource('users', UserController::class)->except('show');
        });
    });
