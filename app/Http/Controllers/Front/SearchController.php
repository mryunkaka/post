<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $keyword = trim((string) $request->string('q'));

        $articles = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->when(
                $keyword !== '',
                function ($query) use ($keyword): void {
                    $query->where(function ($builder) use ($keyword): void {
                        $builder
                            ->where('title', 'like', '%'.$keyword.'%')
                            ->orWhere('excerpt', 'like', '%'.$keyword.'%')
                            ->orWhere('content', 'like', '%'.$keyword.'%');
                    });
                },
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        return view('frontend.search.index', [
            'articles' => $articles,
            'keyword' => $keyword,
            'metaTitle' => $keyword === '' ? 'Pencarian Berita' : 'Hasil Pencarian: '.$keyword,
            'metaDescription' => $keyword === ''
                ? 'Cari artikel terbaru berdasarkan judul, ringkasan, atau isi berita.'
                : 'Hasil pencarian artikel untuk kata kunci '.$keyword.'.',
        ]);
    }
}
