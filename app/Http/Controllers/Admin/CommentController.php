<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $comments = Comment::query()
            ->with(['article:id,title,slug', 'parent:id,content', 'author:id,name'])
            ->when(in_array($status, ['pending', 'approved', 'rejected', 'spam'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'currentStatus' => $status,
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->commentService->approve($comment);

        return back()->with('status', 'Komentar berhasil disetujui.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->commentService->reject($comment);

        return back()->with('status', 'Komentar berhasil ditolak.');
    }

    public function spam(Comment $comment): RedirectResponse
    {
        $this->commentService->markAsSpam($comment);

        return back()->with('status', 'Komentar ditandai sebagai spam.');
    }
}
