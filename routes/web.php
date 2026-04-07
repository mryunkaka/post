<?php

use App\Http\Controllers\Front\ArticleController as FrontArticleController;
use App\Http\Controllers\Front\CategoryController as FrontCategoryController;
use App\Http\Controllers\Front\CommentController as FrontCommentController;
use App\Http\Controllers\Front\HomeController as FrontHomeController;
use App\Http\Controllers\Front\SearchController as FrontSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicMediaController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public')->group(function (): void {
    Route::get('/', FrontHomeController::class)->name('home');
    Route::get('/berita/{articleSlug}', [FrontArticleController::class, 'show'])->name('articles.show');
    Route::post('/berita/{articleSlug}/komentar', [FrontCommentController::class, 'store'])->name('comments.store');
    Route::get('/kategori/{categorySlug}', [FrontCategoryController::class, 'show'])->name('categories.show');
    Route::get('/cari', FrontSearchController::class)->name('search.index');

    Route::get('/media/public/{path}', [PublicMediaController::class, 'show'])
        ->where('path', '.*')
        ->name('media.public');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:admin,editor,wartawan'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
